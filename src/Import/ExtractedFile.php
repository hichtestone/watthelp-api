<?php

declare(strict_types=1);

namespace App\Import;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExtractedFile
{
    private string $path;
    private Worksheet $sheet;

    public function __construct(string $path, Worksheet $sheet)
    {
        $this->path = $path;
        $this->sheet = $sheet;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSheet(): Worksheet
    {
        return $this->sheet;
    }
}