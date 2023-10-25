<?php

declare(strict_types=1);

namespace App\Export;

use App\Entity\User;

interface ExporterInterface
{
    public function export(array $filters, User $user): string;
    public function supports(string $type, string $format): bool;
}