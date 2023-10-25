<?php

declare(strict_types=1);

namespace App\Entity;

interface HasClientInterface
{
    public function getClient(): Client;
}
