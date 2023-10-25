<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ConstraintValidator
{

    public string $class;
    public array $options = [];

    /**
     * ConstraintValidator constructor.
     *
     * @throws AnnotationException
     */
    public function __construct(array $data)
    {
        if (!isset($data['class'])) {
            throw new AnnotationException('Property class is required.');
        }

        $this->class = $data['class'];

        if (isset($data['options']) && is_array($data['options'])) {
            $this->options = $data['options'];
        }
    }
}