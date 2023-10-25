<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Consumption;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class StatsConstraintListValidator extends CollectionValidator
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($data, Constraint $constraint)
    {
        if (!empty($data['period']) && !empty($data['period']['start_day']) &&
            !empty($data['period']['start_month']) && !empty($data['period']['end_day']) && !empty($data['period']['end_month'])) {
            $startDay   = intval($data['period']['start_day']);
            $startMonth = intval($data['period']['start_month']);
            $endDay     = intval($data['period']['end_day']);
            $endMonth   = intval($data['period']['end_month']);

            if ($startDay >= 1 && $startDay <= 31 &&
                $endDay >= 1 && $endDay <= 31 &&
                $startMonth >= 1 && $startMonth <= 12 &&
                $endMonth >= 1 && $endMonth <= 12) {
                
                // check period end is after period start
                if ($endMonth < $startMonth || ($endMonth === $startMonth && $endDay <= $startDay)) {
                    $this->context->buildViolation("Le début de la période doit être inférieur à la fin de la période.")
                        ->atPath('period')
                        ->addViolation();
                }
            }
        }

        // check dates formed by years and period are valid
        if (!empty($data['years']) && is_array($data['years'])) {
            $startDay ??= 1;
            $startMonth ??= 1;
            $endDay ??= 31;
            $endMonth ??= 12;
            foreach ($data['years'] as $year) {
                if (!is_numeric($year)) {
                    continue;
                }
                $start = \DateTime::createFromFormat('Y-n-j', "$year-$startMonth-$startDay");
                $end   = \DateTime::createFromFormat('Y-n-j', "$year-$endMonth-$endDay");
                if (!$start) {
                    $this->context->buildViolation("La date $startDay/$startMonth/$year est invalide.")
                        ->atPath('years')
                        ->addViolation();
                }
                if (!$end) {
                    $this->context->buildViolation("La date $endDay/$endMonth/$year est invalide.")
                        ->atPath('years')
                        ->addViolation();
                }
            }            
        }

        parent::validate($data, $constraint);
    }
}
