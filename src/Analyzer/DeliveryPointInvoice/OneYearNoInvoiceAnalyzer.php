<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\DateFormatService;
use App\Service\LogService;

class OneYearNoInvoiceAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    protected DeliveryPointInvoiceManager $deliveryPointInvoiceManager;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        DeliveryPointInvoiceManager $deliveryPointInvoiceManager
    ) {
        parent::__construct($translationManager, $logger);
        $this->deliveryPointInvoiceManager = $deliveryPointInvoiceManager;
    }

    public function analyze(DeliveryPointInvoice $dpi): void
    {
        $previous = $dpi->getDeliveryPointInvoiceAnalysis()->getPreviousDeliveryPointInvoice();
        $previousYear = $this->getPreviousYear($dpi);
        
        if (!$previous || !$previousYear) {
            $this->ignore(transInfo('previous_delivery_point_invoice_or_last_year_missing'));
            return;
        }

        $invoice = $dpi->getInvoice();
        $deliveryPointInvoices = $this->deliveryPointInvoiceManager->findByFilters(
            $dpi->getDeliveryPoint()->getClient(),
            [
                'delivery_point' => $dpi->getDeliveryPoint(),
                'emitted_at' => [
                    'min' => $min = (clone $invoice->getEmittedAt())->sub(new \DateInterval('P1Y')),
                    'max' => $max = $invoice->getEmittedAt()
                ]
            ]
        );

        if (count($deliveryPointInvoices) === 0) {
            $this->anomaly(
                Anomaly::TYPE_DATE,
                transInfo('no_delivery_point_invoice_emitted_for_more_than_a_year'),
                transInfo('one_year_no_invoice_applied_rules', [
                    'from' => $min,
                    'to' => $max
                ])
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.one_year_no_invoice';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_DEFAULT;
    }

    public function getPriority(): int
    {
        return 1;
    }
}