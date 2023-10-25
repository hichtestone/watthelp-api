<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Message\ImportMessage;
use App\Entity\Notification;

interface ImporterInterface
{
    public function import(string $localFilePath, Import $import, ImportReport $importReport, ImportMessage $message, Notification $notification): ImportReport;
    public function supports(string $type): bool;
}