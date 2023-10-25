<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\File;
use App\Query\Criteria;
use App\Manager\FileManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FileTypeValidator extends ConstraintValidator
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed               $value      The value that should be validated
     * @param Constraint|FileType $constraint The constraint for the validation
     *
     * @throws NonUniqueResultException|\InvalidArgumentException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        /** @var File|null $file */
        $file = $this->fileManager->getByCriteria([new Criteria\File\Id($value)]);
        if (!$file) {
            return;
        }

        if (!\in_array($file->getMime(), $constraint->allowedMimeTypes, true)) {
            $this->context
                ->buildViolation($constraint->errorMessage, ['mime' => $file->getMime()])
                ->addViolation();
        }
    }
}
