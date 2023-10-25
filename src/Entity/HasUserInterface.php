<?php

declare(strict_types=1);

namespace App\Entity;

interface HasUserInterface
{
    public function getUser(): User;
}
