<?php

declare(strict_types=1);

namespace App\Export;

use App\Entity\User;

class ExporterManager
{
    protected iterable $exporters;

    public function __construct(iterable $exporters)
    {
        $this->exporters = $exporters;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function export(string $type, string $format, array $filters, User $user): string
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->supports($type, $format)) {
                return $exporter->export($filters, $user);
            }
        }

        throw new \InvalidArgumentException(sprintf('The type "%s" or format "%s" is not supported.', $type, $format));
    }
}