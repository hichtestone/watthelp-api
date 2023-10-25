<?php

declare(strict_types=1);

namespace App\Request\Validator\DeliveryPoint;

use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Entity\File;
use App\Query\Criteria;
use App\Validator\Constraint\FileType;
use App\Validator\Constraint\SelectableGeneric;
use App\Validator\Constraint\Unique;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class DeliveryPointConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $request = $options['request'] ?? null;
        $existingId = $request ? intval($request->attributes->get('id')) : null;

        $fields = [
            'name' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'reference' => new Required([
                new Type(['type' => 'string']),
                new NotBlank(),
                new Unique([
                    'class' => DeliveryPoint::class,
                    'criteria' => Criteria\DeliveryPoint\Reference::class,
                    'errorMessage' => 'reference_already_exists',
                    'existingId' => $existingId
                ])
            ]),
            'code' => new Optional([
                new Type(['type' => 'string']),
                new Unique([
                    'class' => DeliveryPoint::class,
                    'criteria' => Criteria\DeliveryPoint\Code::class,
                    'errorMessage' => 'code_already_exists',
                    'existingId' => $existingId
                ])
            ]),
            'address' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'latitude' => new Optional(),
            'longitude' => new Optional(),
            'meter_reference' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'power' => new Required([
                new Type(['type' => 'numeric']),
                new NotBlank()
            ]),
            'contract' => new Required([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new SelectableGeneric([
                    'criteria' => Criteria\Contract\Id::class,
                    'entity' => Contract::class,
                    'notFoundMessage' => 'Selected contract doesn\'t exist.'
                ])
            ]),
            'photo' => new Optional([
                new FileType([
                    'allowedMimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                    'errorMessage' => 'only_images_are_accepted',
                ]),
                new SelectableGeneric([
                    'criteria' => Criteria\File\Id::class,
                    'entity' => File::class,
                    'notFoundMessage' => 'Selected photo doesn\'t exist.'
                ])
            ]),
            'description' => new Optional()
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}