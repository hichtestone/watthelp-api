<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\Contract;
use App\Manager\FileManager;
use App\Query\Criteria;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsFileTypeValidForProviderValidator extends ConstraintValidator
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint || !$constraint->file || !$constraint->provider) {
            return;
        }

        $file = $this->fileManager->getByCriteria([new Criteria\File\Id($constraint->file)]);

        if (!$file) {
            return;
        }

        switch ($constraint->provider) {
            case Contract::PROVIDER_DIRECT_ENERGIE:
                
                $excelMimeTypes = [
                    'application/vnd.ms-excel',
                    'application/vnd.ms-office',
                    'application/xls',
                    'application/xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];

                if (!in_array($file->getMime(), $excelMimeTypes)) {
                    $this->context
                        ->buildViolation('file_type_incorrect_expected_excel')
                        ->addViolation();
                }

                break;

            case Contract::PROVIDER_EDF:
            case Contract::PROVIDER_ENGIE:

                $zipMimeTypes = [
                    'application/zip',
                    'application/octet-stream',
                    'application/x-compressed',
                    'application/x-zip-compressed',
                    'multipart/x-zip'
                ];

                if (!in_array($file->getMime(), $zipMimeTypes)) {
                    $this->context
                        ->buildViolation('file_type_incorrect_expected_zip')
                        ->addViolation();
                }

                break;
        }
    }
}