<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\User;
use App\Model\TranslationInfo;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationManager
{
    private TranslationRepository $repository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Translation::class);
        $this->translator = $translator;
    }

    public function getRepo()
    {
        return $this->repository;
    }

    public function translate(object $entity, string $field, TranslationInfo $transInfo, string $domain = null): void
    {
        $messageTranslated = $this->getTranslations($transInfo, $domain);
        foreach (array_diff(User::AVAILABLE_LANGUAGES, [User::LANGUAGE_FR]) as $language) {
            $translation = $messageTranslated[$language];
            // don't add the translation if it's the same as french
            if ($translation === $messageTranslated[User::LANGUAGE_FR]) {
                continue;
            }
            
            $this->repository->translate($entity, $field, $language, $translation);
        }
    }

    public function translateArray(object $entity, string $field, array $translationInfos, string $domain = null): void
    {
        $messagesTranslated = [];
        foreach ($translationInfos as $transInfo) {
            $transInfoTranslated = $this->getTranslations($transInfo, $domain);
            foreach (array_diff(User::AVAILABLE_LANGUAGES, [User::LANGUAGE_FR]) as $language) {
                $messagesTranslated[$language] ??= [];
                $messagesTranslated[$language][] = $transInfoTranslated[$language];
            }
        }

        foreach ($messagesTranslated as $language => $translations) {
            $this->repository->translate($entity, $field, $language, $translations);
        }
    }

    public function getTranslations(TranslationInfo $transInfo, string $domain = null): array
    {
        $result = [];
        foreach (User::AVAILABLE_LANGUAGES as $language) {
            $result[$language] = $this->translator->trans($transInfo->getKey(), $transInfo->getParams(), $domain, $language);
        }
        return $result;
    }

    public function getFrenchTranslation(TranslationInfo $transInfo, string $domain = null): string
    {
        return $this->translator->trans($transInfo->getKey(), $transInfo->getParams(), $domain, User::LANGUAGE_FR);
    }
}