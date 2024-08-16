<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter\Format;

use Laminas\Filter\AbstractFilter;

/**
 * Class MiscSnJustification
 * @package Dvsa\Olcs\Api\Service\Ebsr\Filter\Format
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @template-extends AbstractFilter<array>
 */
class MiscSnJustification extends AbstractFilter
{
    public const MISC_JUSTIFICATION = 'Miscellaneous justification: %s';

    /**
     * Appends the miscellaneous justification field to the unforseen detail field
     *
     * @param array $value
     * @return array
     */
    public function filter($value)
    {
        //if the field isn't set, we don't need to do anything
        if (!isset($value['busShortNotice']['miscJustification'])) {
            return $value;
        }

        $miscJustification = sprintf(self::MISC_JUSTIFICATION, $value['busShortNotice']['miscJustification']);

        if (!isset($value['busShortNotice']['unforseenDetail'])) {
            $value['busShortNotice']['unforseenDetail'] = $miscJustification;
        } else {
            $value['busShortNotice']['unforseenDetail'] .= ' ' . $miscJustification;
        }

        //now we have an unforseen detail, we need to set the corresponding reason to yes
        $value['busShortNotice']['unforseenChange'] = 'Y';

        return $value;
    }
}
