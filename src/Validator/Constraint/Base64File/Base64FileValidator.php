<?php

declare(strict_types=1);

namespace App\Validator\Constraint\Base64File;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class Base64FileValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        $imageString = base64_decode($value);
        if (!$imageString) {
            $this->context->buildViolation($constraint->invalidBase64Data)->addViolation();

            return;
        }

        $imageData = getimagesizefromstring($imageString);
        if (!$imageData) {
            $this->context->buildViolation($constraint->invalidImageData)->addViolation();

            return;
        }

        $imageWidth = $imageData[0];
        $imageHeight = $imageData[1];

        if ($constraint->mimeTypes) {
            if (!is_array($constraint->mimeTypes)) {
                throw new ConstraintDefinitionException('mimeType constraint must be an array');
            }
            if (!in_array($imageData['mime'], $constraint->mimeTypes)) {
                $this->context->buildViolation($constraint->notSupportedType)->addViolation();

                return;
            }
        }

        if ($constraint->minWidth) {
            if (!ctype_digit((string)$constraint->minWidth)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid minimum width.', $constraint->minWidth));
            }
            if ($imageWidth < $constraint->minWidth) {
                $this->context->buildViolation($constraint->tooLowWidth)->addViolation();

                return;
            }
        }

        if ($constraint->maxWidth) {
            if (!ctype_digit((string)$constraint->maxWidth)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum width.', $constraint->maxWidth));
            }
            if ($imageWidth > $constraint->maxWidth) {
                $this->context->buildViolation($constraint->tooHighWidth)->addViolation();

                return;
            }
        }

        if ($constraint->minHeight) {
            if (!ctype_digit((string)$constraint->minHeight)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid minimum height.', $constraint->minHeight));
            }
            if ($imageHeight < $constraint->minHeight) {
                $this->context->buildViolation($constraint->tooLowHeight)->addViolation();

                return;
            }
        }

        if ($constraint->maxHeight) {
            if (!ctype_digit((string)$constraint->maxHeight)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum height.', $constraint->maxHeight));
            }
            if ($imageHeight > $constraint->maxHeight) {
                $this->context->buildViolation($constraint->tooHighHeight)->addViolation();

                return;
            }
        }

        if ($constraint->maxSize) {
            $maxSize = intval(str_replace('M', '000000', str_replace('K', '000', $constraint->maxSize)));
            if (strlen(base64_decode($value)) > $maxSize) {
                $this->context->buildViolation($constraint->tooBigFile)->addViolation();

                return;
            }
        }
    }
}
