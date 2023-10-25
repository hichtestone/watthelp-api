<?php

declare(strict_types=1);

namespace App\Validator\Constraint\Base64File;

use Symfony\Component\Validator\Constraint;

class Base64File extends Constraint
{
    public string $invalidBase64Data = 'invalid base64 data';
    public string $invalidFileData = 'invalid file data';
    public string $notSupportedType = 'file type not supported';
    public string $tooBigFile = 'too big file';
    public string $tooLowWidth = 'too low width';
    public string $tooHighWidth = 'too high width';
    public string $tooLowHeight = 'too low hight';
    public string $tooHighHeight = 'too high height';

    public array $mimeTypes = [];
    public ?int $minWidth = null;
    public ?int $maxWidth = null;
    public ?int $minHeight = null;
    public ?int $maxHeight = null;
    public string $maxSize = '';
}
