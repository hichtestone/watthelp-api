<?php

declare(strict_types=1);

namespace App\Import\Verifier;

use App\Exceptions\ImportException;
use App\Import\SpreadsheetReaderTrait;
use App\Manager\InvoiceManager;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \Exception;

abstract class AbstractVerifier
{
    use SpreadsheetReaderTrait;

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function verifyFileColumns(Worksheet $sheet, string $fileName, Collection $columns): array
    {
        $errors = [];
        foreach ($columns as $column) {
            $cellValue = $sheet->getCell($column->getCell())->getValue();
            if ($cellValue !== $column->getExpectedValue()) {
                $errors[] = $this->createError($column->getCell(), $cellValue, $column->getExpectedValue(), $fileName);
            }
        }

        return $errors;
    }

    protected function createError(string $cell, ?string $actualValue, string $expectedValue, string $fileName = ''): string
    {
        if ($fileName) {
            $fileName = " $fileName";
        }
        return $this->translator->trans('incorrect_file_unexpected_value', [
            'filename' => $fileName,
            'cell' => $cell,
            'actual_value' => $actualValue,
            'expected_value' => $expectedValue
        ]);
    }
}