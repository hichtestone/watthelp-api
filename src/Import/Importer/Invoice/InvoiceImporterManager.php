<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;
use App\Entity\Invoice;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceImporterManager
{
    private iterable $importers;
    private TranslatorInterface $translator;

    public function __construct(iterable $importers, TranslatorInterface $translator)
    {
        $this->importers = $importers;
        $this->translator = $translator;
    }

    /**
     * @return Invoice[]
     * @throws \InvalidArgumentException
     */
    public function import(string $filePath, string $provider, Client $client, array $invoiceReferencesToReimport): array
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports($provider)) {
                return $importer->import($filePath, $client, $invoiceReferencesToReimport);
            }
        }

        throw new \InvalidArgumentException($this->translator->trans('provider_not_supported', ['provider' => $provider]));
    }
}