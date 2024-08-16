<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter\Format;

use Laminas\Filter\AbstractFilter as AbstractFilter;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 * @template-extends AbstractFilter<array>
 */
class MemberStateCode extends AbstractFilter
{
    /**
     * Change 'UK' to 'GB'
     *
     * @param array $value input value
     *
     * @return array
     */
    public function filter($value)
    {
        if (strtoupper((string) $value['memberStateCode']) === 'UK') {
            $value['memberStateCode'] = 'GB';
        }

        return $value;
    }
}
