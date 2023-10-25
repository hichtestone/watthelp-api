<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Amounts;

use App\Query\Criteria;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class StatsConstraintListValidator extends CollectionValidator
{
    public function validate($data, Constraint $constraint)
    {
        if (isset($data['start']) && isset($data['end'])) {
            $start = \DateTime::createFromFormat('Y-m-d', $data['start']);
            $end   = \DateTime::createFromFormat('Y-m-d', $data['end']);
            if ($start && $end) {
                if ($end <= $start) {
                    $this->context->buildViolation('period_start_must_be_before_period_end')
                        ->addViolation();
                }
            }
        }

        parent::validate($data, $constraint);
    }
}
