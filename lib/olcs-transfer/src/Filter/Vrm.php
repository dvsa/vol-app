<?php

namespace Dvsa\Olcs\Transfer\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * VRM Filter
 *
 * Parses a VRM stripping whitespace, converting to UPPERCASE and translates commonly mistyped / printed old plates.
 *
 * @template-extends AbstractFilter<array>
 */
class Vrm extends AbstractFilter
{
    /**
     * Translate some commonly mis-typed / printed old plates
     */
    protected $translations = [
        'GO' => 'G0',
        'HSO' => 'HS0',
        'IG' => '1G',
        'II' => '11',
        'IRAQ' => '1RAQ',
        'IS' => '1S',
        'IV' => '1V',
        'ICZS' => '1CZS',
        'LMO' => 'LM0',
        'QLDI' => 'QLD1',
        'QTRI' => 'QTR1',
        'QUEI' => 'QUE1',
        'RGO' => 'RG0',
        'SO' => 'S0',
        'SYO' => 'SY0',
        'VO' => 'V0',
        'VSO' => 'VS0'
    ];

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return string
     */
    #[\Override]
    public function filter($value)
    {
        // Strip all whitespace
        $value = preg_replace('/\s+/', '', $value);

        // Convert to uppercase
        $value = strtoupper($value);

        // Translate some commonly mis-typed / printed old plates
        if (isset($this->translations[$value])) {
            $value = $this->translations[$value];
        }

        return $value;
    }
}
