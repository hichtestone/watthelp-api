<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Message\ImportMessage;
use App\Entity\Notification;

class ImporterManager
{
    private iterable $importers;

    public function __construct(iterable $importers)
    {
        $this->importers = $importers;
    }

    public function import(string $filePath, Import $import, ImportReport $importReport, ImportMessage $importMessage, Notification $notification): ImportReport
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports($import->getType())) {
                return $importer->import($filePath, $import, $importReport, $importMessage, $notification);
            }
        }

        throw new \InvalidArgumentException(sprintf('The type "%s" is not supported.', $import->getType()));
    }
}