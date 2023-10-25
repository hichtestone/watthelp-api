<?php

declare(strict_types=1);

namespace App\Request\Validator;

use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class DeleteMultipleConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        if (empty($options['type'])) {
            throw new \LogicException('You must specify the "type" in the ConstraintValidator annotation to use this validator.');
        }
        if (empty($options['criteria'])) {
            throw new \LogicException('You must specify the "criteria" in the ConstraintValidator annotation to use this validator.');
        }

        $fields = [
            'ids' => new Required(new AtLeastOneOf(
                [
                    new All([
                        new NotBlank(),
                        new Type(['type' => 'numeric']),
                        new Constraint\SelectableGeneric([
                            'criteria' => $options['criteria'],
                            'entity' => $options['type'],
                            'belongUser' => isset($options['belongUser']) && $options['belongUser']
                        ])
                    ]),
                    new IdenticalTo('*')
                ]
            )),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
