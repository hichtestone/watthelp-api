<?php

declare(strict_types=1);

namespace App\Analyzer;

use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\Analysis\ItemAnalysis;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;

abstract class AbstractAnalyzer
{
    private ItemAnalysis $itemAnalysis;
    private TranslationManager $translationManager;
    private LogService $logger;

    public function __construct(TranslationManager $translationManager, LogService $logger)
    {
        $this->translationManager = $translationManager;
        $this->logger = $logger;
    }

    public function setItemAnalysis(ItemAnalysis $itemAnalysis): void
    {
        $this->itemAnalysis = $itemAnalysis;
    }

    protected function ignore(TranslationInfo $message, ?string $field = null): void
    {
        $this->logger->info('Analyzer ' . $this->getName() . ' ignored with message ' . $message);
        $this->itemAnalysis->setStatus(Analysis::STATUS_WARNING);
        $this->itemAnalysis->setField($field);
        $this->translationManager->translate($this->itemAnalysis, 'messages', $message);
        $this->itemAnalysis->addMessage($this->translationManager->getFrenchTranslation($message));
        $analysis = $this->itemAnalysis->getAnalysis();
        if ($analysis->getStatus() === Analysis::STATUS_PROCESSING) {
            $analysis->setStatus(Analysis::STATUS_WARNING);
        }
        $dpia = $this->itemAnalysis->getDeliveryPointInvoiceAnalysis();
        if ($dpia->getStatus() === Analysis::STATUS_PROCESSING) {
            $dpia->setStatus(Analysis::STATUS_WARNING);
        }
    }

    /**
     * Add union type to appliedRules once we support PHP8
     * @param TranslationInfo|string|null $appliedRules
     */
    protected function anomaly(
        string $type,
        TranslationInfo $message,
        $appliedRules = null,
        ?string $currentValue = null,
        ?string $oldValue = null,
        ?TranslationInfo $expectedValue = null,
        ?string $field = null,
        ?AmountDiff $diff = null
    ): void {
        $this->logger->info('Analyzer ' . $this->getName() . ' anomaly with message ' . $message . ' and applied rules ' . ($appliedRules ?? ''));

        $anomaly = new Anomaly();
        $anomaly->setContent($this->translationManager->getFrenchTranslation($message));
        $this->translationManager->translate($anomaly, 'content', $message);
        $anomaly->setType($type);
        $anomaly->setStatus(Anomaly::STATUS_PROCESSING);
        if ($diff) {
            $anomaly->setTotal($diff->getAmount());
            $anomaly->setTotalPercentage($diff->getPercentage());
            $anomaly->setProfit($diff->getProfit());
        }
        $anomaly->setCurrentValue($currentValue);
        $anomaly->setOldValue($oldValue);

        if (is_a($appliedRules, TranslationInfo::class)) {
            $anomaly->setAppliedRules($this->translationManager->getFrenchTranslation($appliedRules));
            $this->translationManager->translate($anomaly, 'appliedRules', $appliedRules);
        } else {
            $anomaly->setAppliedRules($appliedRules);
        }

        $this->itemAnalysis->addMessage($this->translationManager->getFrenchTranslation($message));

        $this->itemAnalysis->setField($field);
        if ($expectedValue) {
            $expectedValueFrenchTranslation = $this->translationManager->getFrenchTranslation($expectedValue);
            $anomaly->setExpectedValue($expectedValueFrenchTranslation);
            $this->translationManager->translate($anomaly, 'expectedValue', $expectedValue);

            $this->itemAnalysis->addMessage($expectedValueFrenchTranslation);
            $this->translationManager->translateArray($this->itemAnalysis, 'messages', [$message, $expectedValue]);
        } else {
            $this->translationManager->translateArray($this->itemAnalysis, 'messages', [$message]);
        }

        $this->itemAnalysis->setStatus(Analysis::STATUS_ERROR);
        $this->itemAnalysis->getAnalysis()->setStatus(Analysis::STATUS_ERROR);
        $this->itemAnalysis->getDeliveryPointInvoiceAnalysis()->setStatus(Analysis::STATUS_ERROR);
        $this->itemAnalysis->setAnomaly($anomaly);
    }

    /**
     * @param int $total
     * @param int|float $totalMin
     * @param int|float $totalMax
     * @return AmountDiff
     */
    protected function getAmountDiff(int $total, $totalMin, $totalMax): AmountDiff
    {
        $profit = $total < $totalMin ? Anomaly::PROFIT_CLIENT : Anomaly::PROFIT_PROVIDER;
        $totalMin = intval(round($totalMin));
        $totalMax = intval(round($totalMax));
        $minDiff = abs($total - $totalMin);
        $maxDiff = abs($total - $totalMax);
        $closest = $minDiff < $maxDiff ? $totalMin : $totalMax;
        $amount = intval(round(min($minDiff, $maxDiff)));
        $percent = round((abs($closest-$total)/$closest)*100, 2);
        return new AmountDiff($amount, $percent, $profit);
    }

    public function getPreviousYear(DeliveryPointInvoice $dpi): ?DeliveryPointInvoice
    {
        $previousDate = clone $dpi->getInvoice()->getEmittedAt();
        $previousDate->modify('-1 year');
        return $this->deliveryPointInvoiceManager->getPrevious($dpi, $previousDate);
    }

    public function getDaysDiff(\DateTimeInterface $previous, \DateTimeInterface $current): int
    {
        return abs($current->diff($previous)->days);
    }

    public function getMonthDiff(\DateTimeInterface $previous, \DateTimeInterface $current): float
    {
        $days = $current->diff($previous)->days;
        return round(abs($days / 30));
    }

    public function supportsAnalysis(DeliveryPointInvoice $deliveryPointInvoice): bool
    {
        return true;
    }

    public function stopChain(): bool
    {
        return false;
    }

    public function getPriority(): int
    {
        return 1;
    }
}
