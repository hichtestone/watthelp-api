<?php

declare(strict_types=1);

namespace App\Analyzer;

use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\Invoice\Analysis\ItemAnalysis;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Service\LogService;

class AnalyzerChain
{
    protected array $analyzers = [];
    protected LogService $logger;

    public function __construct(iterable $analyzers, LogService $logger)
    {
        $this->logger = $logger;
        foreach ($analyzers as $analyzer) {
            $this->addAnalyzer($analyzer);
        }
    }

    public function addAnalyzer(AnalyzerInterface $analyzer): void
    {
        $this->analyzers[$analyzer->getPriority()][] = $analyzer;
    }

    public function getAnalyzers(): array
    {
        return $this->analyzers;
    }

    /**
     * @throws \Exception
     */
    public function analyse(DeliveryPointInvoice $dpi, Analysis $analysis, DeliveryPointInvoiceAnalysis $dpia)
    {
        $shouldStop = false;
        ksort($this->analyzers);

        foreach ($this->analyzers as $priority => $prioritizedAnalyzers) {
            if (true === $shouldStop) {
                continue;
            }
            /** @var AnalyzerInterface $prioritizedAnalyzer */
            foreach ($prioritizedAnalyzers as $prioritizedAnalyzer) {
                if ($prioritizedAnalyzer->supportsAnalysis($dpi)) {
                    $this->logger->info('Analyzer ' . $prioritizedAnalyzer->getName() . ' on delivery point invoice ' . $dpi->getId());
                    $itemAnalysis = new ItemAnalysis();
                    $itemAnalysis->setAnalyzer($prioritizedAnalyzer->getName());
                    $itemAnalysis->setGroup($prioritizedAnalyzer->getGroup());
                    $itemAnalysis->setAnalysis($analysis);
                    $dpia->addItemAnalysis($itemAnalysis);

                    $prioritizedAnalyzer->setItemAnalysis($itemAnalysis);
                    $prioritizedAnalyzer->analyze($dpi);

                    if ($prioritizedAnalyzer->stopChain()) {
                        $shouldStop = true;
                    }
                }
            }
        }
    }
}
