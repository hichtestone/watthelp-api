<?php

declare(strict_types=1);

namespace App\DoctrineTypes\User;

use App\DoctrineTypes\EnumType;
use App\Entity\User;

class EnumTypeLanguage extends EnumType
{
    protected string $name = 'enumTypeLanguage';
    protected array $values = User::AVAILABLE_LANGUAGES;
}