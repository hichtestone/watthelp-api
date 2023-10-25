<?php

declare(strict_types=1);

namespace App\Entity\Invoice;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

/**
 * @ORM\Table(name="anomaly_translations", indexes={
 *      @ORM\Index(name="anomaly_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass=TranslationRepository::class)
 */
class AnomalyTranslation extends AbstractTranslation
{
}