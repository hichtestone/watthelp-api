<?php
declare(strict_types=1);

namespace App\Validator\Constraint\User;

use Symfony\Component\Validator\Constraint;

class PasswordSecurity extends Constraint
{
    public string $tooShort = '{{length}} caractères';
    public string $missingLetters = 'une lettre';
    public string $missingCaseDiff = 'une lettre majuscule';
    public string $missingNumbers = 'un nombre';
    public string $missingSpecialCharacter = 'un caractère spécial';
}