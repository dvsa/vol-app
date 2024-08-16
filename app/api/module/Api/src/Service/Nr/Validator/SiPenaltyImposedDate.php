<?php

namespace Dvsa\Olcs\Api\Service\Nr\Validator;

use Laminas\Validator\Exception;
use Laminas\Validator\Date;
use Laminas\Validator\AbstractValidator as LaminasAbstractValidator;

/**
 * Class SiPenaltyImposedDate
 * @package Dvsa\Olcs\Api\Service\Nr\Validator
 */
class SiPenaltyImposedDate extends LaminasAbstractValidator
{
    public const IMPOSED_PENALTY_INCORRECT_DECISION_START_DATE = 'imposed-penalty-incorrect-decision-start-date';
    public const IMPOSED_PENALTY_INCORRECT_DECISION_END_DATE = 'imposed-penalty-incorrect-decision-end-date';
    public const IMPOSED_PENALTY_INCORRECT_START_DATE = 'imposed-penalty-incorrect-start-date';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::IMPOSED_PENALTY_INCORRECT_DECISION_START_DATE => 'Imposed penalty decision date later than start date',
        self::IMPOSED_PENALTY_INCORRECT_DECISION_END_DATE => 'Imposed penalty decision date later than end date',
        self::IMPOSED_PENALTY_INCORRECT_START_DATE => 'Imposed penalty start date must be before end date',
    ];

    /**
     * Checks the imposed penalty types exist and that dates are in the correct order
     *
     * @param array $value value being tested
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (isset($value['imposedErrus'])) {
            foreach ($value['imposedErrus'] as $dates) {
                $hasStartDate = $dates['startDate'] instanceof \DateTime;
                $hasEndDate = $dates['endDate'] instanceof \DateTime;

                if ($hasStartDate) {
                    if ($dates['finalDecisionDate'] > $dates['startDate']) {
                        $this->error(self::IMPOSED_PENALTY_INCORRECT_DECISION_START_DATE);
                        return false;
                    }
                }

                if ($hasEndDate) {
                    if ($dates['finalDecisionDate'] > $dates['endDate']) {
                        $this->error(self::IMPOSED_PENALTY_INCORRECT_DECISION_END_DATE);
                        return false;
                    }
                }

                if ($hasStartDate && $hasEndDate && ($dates['startDate'] > $dates['endDate'])) {
                    $this->error(self::IMPOSED_PENALTY_INCORRECT_START_DATE);
                    return false;
                }
            }
        }

        return true;
    }
}
