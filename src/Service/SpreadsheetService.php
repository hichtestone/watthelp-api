<?php

declare(strict_types=1);

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetService
{

    public function makeXlsxSheets(string $filePath, array $sheetNames = []): array
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader = $reader->load($filePath);
        $sheets = [];

        foreach ($sheetNames as $sheetName) {
            $sheets[$sheetName] = $reader->getSheetByName($sheetName);
        }

        return $sheets;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function makeXslxSheet(string $filePath, ?string $sheetName = null): Worksheet
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader = $reader->load($filePath);

        return is_null($sheetName) ? $reader->getActiveSheet() : $reader->getSheetByName($sheetName);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function makeCsvSheet(string $filePath): Worksheet
    {
        $reader = new Csv();
        $reader->setReadDataOnly(true);
        $reader->setDelimiter(';');
        $reader->setSheetIndex(0);
        $reader->setInputEncoding('ISO-8859-1');

        return $reader->load($filePath)->getActiveSheet();
    }
}