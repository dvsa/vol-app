<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Address view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class Address extends AbstractHelper
{
    /**
     * Get the HTML to render an address array
     *
     *
     * @return string HTML
     */
    public function __invoke(array $address, array $fields = null, $glue = ', ')
    {
        $parts = [];

        $address['countryCode'] = $address['countryCode']['id'] ?? null;

        if (!isset($fields)) {
            $fields = [
                'addressLine1',
                'addressLine2',
                'addressLine3',
                'town',
                'postcode',
                'countryCode',
            ];
        }

        foreach ($fields as $item) {
            if (!isset($address[$item])) {
                continue;
            }
            if (empty($address[$item])) {
                continue;
            }
            $parts[] = htmlspecialchars($address[$item]);
        }

        return implode($glue, $parts);
    }
}
