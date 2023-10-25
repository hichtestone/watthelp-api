<?php

namespace App\Request\Validator\Contract;

use App\Manager\PricingManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class ContractConstraintListValidator extends CollectionValidator
{
    private PricingManager $pricingManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        PricingManager $pricingManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->pricingManager = $pricingManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (array_key_exists('pricing_ids', $value) && is_array($value['pricing_ids']) && !empty($value['pricing_ids'])) {
            $authenticatedUser = $this->tokenStorage->getToken()->getUser();
            $pricings = iterator_to_array($this->pricingManager->findByFilters($authenticatedUser->getClient(), ['id' => $value['pricing_ids']]));
            $pricingsCount = count($pricings);

            if ($pricingsCount === count($value['pricing_ids'])) {
                $overlappingPricings = [];
                // check every pricing with every other pricing to make sure they don't overlap
                foreach ($pricings as $index => $pricing) {
                    for ($i = 0; $i < $pricingsCount; ++$i) {
                        if ($i === $index) {
                            continue;
                        }
                        if (in_array("{$pricing->getId()}-{$pricings[$i]->getId()}", $overlappingPricings)) {
                            continue;
                        }
                        if ($pricing->doDatesOverlap($pricings[$i])) {
                            $this->context->buildViolation('pricings_overlap', ['first_pricing' => $pricing->getId(), 'second_pricing' => $pricings[$i]->getId()])
                                ->atPath('[pricing_ids]')
                                ->addViolation();

                            array_push($overlappingPricings, "{$pricing->getId()}-{$pricings[$i]->getId()}", "{$pricings[$i]->getId()}-{$pricing->getId()}");
                        }
                    }
                }
            }
        }

        parent::validate($value, $constraint);
    }
}
