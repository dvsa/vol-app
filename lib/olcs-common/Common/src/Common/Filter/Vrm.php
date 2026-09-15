<?php

/**
 * VRM filter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * VRM filter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 *
 * @template-extends AbstractFilter<string, string>
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
     * @param  string $input
     * @return string
     */
    #[\Override]
    public function filter($input)
    {
        // ab04 CVA -> AB04CVA
        $input = strtoupper(str_replace(' ', '', $input));

        return $this->translations[$input] ?? $input;
    }
}
