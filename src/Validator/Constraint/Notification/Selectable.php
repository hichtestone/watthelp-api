<?php

declare(strict_types=1);

namespace App\Validator\Constraint\Notification;

use Symfony\Component\Validator\Constraint;

class Selectable extends Constraint
{
    public string $notExistingNotification = 'Selected notification does not exist.';
}
