<?php

declare(strict_types=1);

namespace App\Request\Validator\User;

use App\Entity\User;
use App\Query\Criteria;
use App\Validator\Constraint\Unique;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

trait UserConstraintTrait
{
    public function getEmailConstraint(?int $existingId = null): array
    {
        return [
            new NotBlank(),
            new Email(),
            new Unique([
                'class' => User::class,
                'criteria' => Criteria\User\Email::class,
                'errorMessage' => 'email_address_already_used',
                'existingId' => $existingId
            ]),
        ];
    }
}
