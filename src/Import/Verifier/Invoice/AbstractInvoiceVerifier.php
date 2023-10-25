<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Exceptions\ImportInvoiceAlreadyExistingException;
use App\Entity\Client;
use App\Import\Verifier\AbstractVerifier;
use App\Manager\InvoiceManager;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class AbstractInvoiceVerifier extends AbstractVerifier
{
    protected InvoiceManager $invoiceManager;

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function verifyFileInvoicesHaveNotAlreadyBeenImported(Worksheet $sheet, string $fileName, string $invoiceReferenceColumnName, int $firstDataRow, Client $client): void
    {
        $lastRowIndex = $sheet->getHighestDataRow($invoiceReferenceColumnName);
        $invoiceReferencesInFile = $sheet->rangeToArray($invoiceReferenceColumnName.strval($firstDataRow).':'.$invoiceReferenceColumnName.strval($lastRowIndex));
        $invoiceReferencesInFile = array_unique(array_map(fn($item) => $item[0], $invoiceReferencesInFile));

        $alreadyExistingInvoices = iterator_to_array($this->invoiceManager->findByFilters($client, ['references' => $invoiceReferencesInFile]));

        if (!empty($alreadyExistingInvoices)) {
            $invoiceReferences = array_map(fn ($invoice) => $invoice->getReference(), $alreadyExistingInvoices);
            $errorMessage = $this->translator->trans('invoices_already_imported', [
                'count_invoices' => count($invoiceReferences),
                'invoices' => implode(', ', $invoiceReferences)
            ]);
            throw new ImportInvoiceAlreadyExistingException($alreadyExistingInvoices, [$errorMessage]);
        }
    }
}