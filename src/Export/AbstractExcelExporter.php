<?php

declare(strict_types=1);

namespace App\Export;

use App\Entity\Client;
use App\Service\S3Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class AbstractExcelExporter
{
    protected S3Uploader $uploader;

    protected function writeHeaders(Worksheet $sheet, array $headers): void
    {
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
        }

        $lastItem = $sheet->getHighestColumn(1);
        $sheet->getStyle(sprintf('A1:%s1', $lastItem))->getFont()->setBold(true);
    }

    protected function initSpreadsheet(string $title, array $headers): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        if (null !== $headers) {
            $this->writeHeaders($sheet, $headers);
        }

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function spreadsheetContent(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /**
     * @throws \Exception
     */
    protected function upload(Spreadsheet $spreadsheet, Client $client, string $path, string $filenamePrefix): string
    {
        $spreadsheetContent = $this->spreadsheetContent($spreadsheet);

        $filepath = "{$client->getId()}/$path";
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->format('Y_m_d_H:i:s');

        $url = $this->uploader->uploadContent(
            $filepath,
            $spreadsheetContent,
            "{$filenamePrefix}_{$now}.xlsx",
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        return $url;
    }
}