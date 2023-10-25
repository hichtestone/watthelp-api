<?php

declare(strict_types=1);

namespace App\Import;

use App\Service\DateFormatService;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \DateTime;

trait SpreadsheetReaderTrait
{
    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function getCalculatedCellValue(Worksheet $sheet, string $column, string $rowIndex)
    {
        $cellValue = $sheet->getCell($column.$rowIndex)->getCalculatedValue();
        if ($cellValue === '') {
            return null;
        }
        return $cellValue;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function getCellValue(Worksheet $sheet, string $column, string $rowIndex)
    {
        $cellValue = $sheet->getCell($column.$rowIndex)->getValue();
        if ($cellValue === '') {
            return null;
        }
        return $cellValue;
    }

    protected function dataToInt($data): ?int
    {
        if (is_null($data)) {
            return null;
        }
        if (is_string($data)) {
            $data = trim($data);
        }
        return intval($data);
    }

    protected function floatToInt($data, int $power = 2): ?int
    {
        if (is_null($data)) {
            return null;
        }
        if (is_string($data)) {
            $data = floatval(str_replace(',', '.', $data));
        }
        
        return intval(round($data * 10**$power));
    }

    protected function amountToInt($amount, bool $isAmountInCents = false): ?int
    {
        return $this->floatToInt($amount, $isAmountInCents ? 5 : 7);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function dataToDatetime($inputDate): ?DateTime
    {
        if (is_null($inputDate)) {
            return null;
        }
        if (is_int($inputDate)) {
            $datetime = Date::excelToDateTimeObject($inputDate);
            return $datetime;
        }
        $datetime = DateTime::createFromFormat(DateFormatService::IMPORT, trim($inputDate));
        if ($datetime === false) {
            throw new \InvalidArgumentException("La date $inputDate est invalide.");
        }
        return $datetime;
    }

    protected function dataToString($data): ?string
    {
        return is_null($data) ? null : trim(strval($data));
    }
}