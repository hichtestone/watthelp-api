<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Analyzer\AnalyzerChain;
use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\User;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\AnalysisManager;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\NotificationManager;
use App\Manager\UserManager;
use App\Message\AnalyzeInvoiceMessage;
use App\Query\Criteria;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AnalyzeInvoiceMessageHandler implements MessageHandlerInterface
{
    private InvoiceManager $invoiceManager;
    private AnalysisManager $analysisManager;
    private UserManager $userManager;
    private NotificationManager $notificationManager;
    private DeliveryPointInvoiceManager $deliveryPointInvoiceManager;
    private AnalyzerChain $analyzerChain;
    private EventDispatcherInterface $dispatcher;
    private EntityManagerInterface $entityManager;
    private LogService $logService;
    private User $user;

    public function __construct(
        InvoiceManager $invoiceManager,
        AnalysisManager $analysisManager,
        UserManager $userManager,
        NotificationManager $notificationManager,
        AnalyzerChain $analyzerChain,
        DeliveryPointInvoiceManager $deliveryPointInvoiceManager,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        LogService $logService
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->analysisManager = $analysisManager;
        $this->userManager = $userManager;
        $this->notificationManager = $notificationManager;
        $this->analyzerChain = $analyzerChain;
        $this->deliveryPointInvoiceManager = $deliveryPointInvoiceManager;
        $this->dispatcher = $dispatcher;
        $this->entityManager = $entityManager;
        $this->logService = $logService;
    }


    /**
     * @throws NotFoundResourceException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function __invoke(AnalyzeInvoiceMessage $message)
    {
        $this->user = $this->userManager->getByCriteria(null, [new Criteria\User\Id($message->getUserId())]);
        if (!$this->user) {
            throw new NotFoundResourceException(sprintf('User "%s" does not exist.', $message->getUserId()));
        }

        $invoiceFilters = $message->getFilters();
        $invoices = $this->invoiceManager->findByFilters($this->user->getClient(), $invoiceFilters);
        $countInvoices = count($invoices);

        if (!$countInvoices) {
            throw new NotFoundResourceException('Could not find any invoice.');
        }

        $this->logService->initProgression($countInvoices);
        foreach ($invoices as $index => $invoice) {
            $this->analyzeInvoice($invoice->getId(), $invoice->getReference());
        }

        if ($countInvoices > 1) {
            $this->logService->sendNotification($this->user, null, 'Toutes les analyses ont été effectuées.');
        }
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function analyzeInvoice(int $invoiceId, string $invoiceRef): void
    {
        $notification = $this->logService->sendNotification($this->user, null, "L'analyse de la facture $invoiceRef est en cours.", null, null, true);

        $invoice = $this->invoiceManager->getByCriteria($this->user->getClient(), [new Criteria\Invoice\Id($invoiceId)]);

        $this->logService->info("Retrieved invoice $invoiceRef");

        if ($analysis = $invoice->getAnalysis()) {
            $this->analysisManager->delete($analysis);
            $invoice->setAnalysis(null);
            $this->entityManager->clear();

            $invoice = $this->invoiceManager->getByCriteria($invoice->getClient(), [new Criteria\Invoice\Id($invoiceId)]);
            $this->user = $this->userManager->getByCriteria($this->user->getClient(), [new Criteria\User\Id($this->user)]);
            $notification = $this->notificationManager->getByCriteria([new Criteria\Notification\Id($notification)]);
        }

        $analysis = new Analysis();
        $analysis->setInvoice($invoice);
        $invoice->setAnalysis($analysis);

        foreach ($invoice->getDeliveryPointInvoices() as $dpi) {
            $this->logService->info('Analyzing delivery point invoice ' . $dpi->getId());
            $previousDpi = $this->deliveryPointInvoiceManager->getPrevious($dpi);
            $dpia = new DeliveryPointInvoiceAnalysis();
            $dpia->setDeliveryPointInvoice($dpi);
            $dpia->setPreviousDeliveryPointInvoice($previousDpi);
            $dpi->setDeliveryPointInvoiceAnalysis($dpia);
            $this->analyzerChain->analyse($dpi, $analysis, $dpia);
            $analysis->addDeliveryPointInvoiceAnalysis($dpia);
        }

        $this->logService->info('Finished analyzing delivery point invoices');

        $this->invoiceManager->update($invoice);

        $this->logService->sendNotification($this->user, $notification,
            "Le rapport d'analyse de la facture {$invoice->getReference()} est disponible.",
            [
                'report_id' => $invoice->getAnalysis()->getId(),
                'report_type' => 'analysis'
            ]
        );
    }
}