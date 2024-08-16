<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter\Format;

use Laminas\Filter\AbstractFilter as AbstractFilter;

/**
 * Class IsExecuted
 * @package Dvsa\Olcs\Api\Service\Nr\Filter\Format
 * @template-extends AbstractFilter<array>
 */
class IsExecuted extends AbstractFilter
{
    public const YES_EXECUTED_KEY = 'pen_erru_imposed_executed_yes';
    public const NO_EXECUTED_KEY = 'pen_erru_imposed_executed_no';
    public const UNKNOWN_EXECUTED_KEY = 'pen_erru_imposed_executed_un';

    /**
     * Returns the result of filtering $value
     *
     * @param  array $value
     * @return array
     */
    public function filter($value)
    {
        if (empty($value['imposedErrus'])) {
            return $value;
        }

        foreach ($value['imposedErrus'] as $key => $erru) {
            //lowercase the value to make sure we always get a match
            $executed = strtolower((string) $erru['executed']);

            $newValue = match ($executed) {
                'yes' => self::YES_EXECUTED_KEY,
                'no' => self::NO_EXECUTED_KEY,
                default => self::UNKNOWN_EXECUTED_KEY,
            };

            $value['imposedErrus'][$key]['executed'] = $newValue;
        }

        return $value;
    }
}
