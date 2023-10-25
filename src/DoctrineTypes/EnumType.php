<?php

declare(strict_types=1);

namespace App\DoctrineTypes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends Type
{
    protected string $name;
    protected array $values = [];

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $values = array_map(function ($val) {
            return "'".$val."'";
        }, $this->values);

        return 'ENUM('.implode(', ', $values).") COMMENT '(DC2Type:".$this->name.")'";
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (in_array($value, $this->values) || '' === $value || null === $value) {
            return $value;
        }
        throw new \InvalidArgumentException(sprintf("Invalid '%s' value for '%s' type.", $value, $this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}