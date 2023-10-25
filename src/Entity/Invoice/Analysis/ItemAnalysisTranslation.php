<?php

declare(strict_types=1);

namespace App\Entity\Invoice\Analysis;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

/**
 * @ORM\Table(name="item_analysis_translations", indexes={
 *      @ORM\Index(name="item_analysis_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass=TranslationRepository::class)
 */
class ItemAnalysisTranslation extends AbstractTranslation
{
}