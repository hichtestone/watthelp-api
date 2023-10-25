<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Entity\Client;
use App\Entity\Import;
use Symfony\Contracts\Translation\TranslatorInterface;

class VerifierManager
{
    private iterable $verifiers;
    private TranslatorInterface $translator;

    public function __construct(iterable $verifiers, TranslatorInterface $translator)
    {
        $this->verifiers = $verifiers;
        $this->translator = $translator;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function verify(string $filePath, Import $import, array $invoiceReferencesToReimport, Client $client): void
    {
        foreach ($this->verifiers as $verifier) {
            if ($verifier->supports($import->getProvider())) {
                $verifier->verify($filePath, $invoiceReferencesToReimport, $client);
                return;
            }
        }

        throw new \InvalidArgumentException($this->translator->trans('provider_not_supported', ['provider' => $provider]));
    }
}