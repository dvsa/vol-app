<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * PersonName view helper
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class PersonName extends AbstractHelper
{
    /**
     * Get the HTML to render a persons name
     *
     * @param array $address
     *
     * @return string HTML
     */
    public function __invoke($person = [], $fields = null)
    {
        if ($fields === null) {
            $fields = [
                'title',
                'forename',
                'familyName',
            ];
        }

        $parts = [];

        foreach ($fields as $item) {
            if (empty($person[$item])) {
                continue;
            }

            if (is_array($person[$item]) && isset($person[$item]['description'])) {
                $parts[] = htmlspecialchars((string) $person[$item]['description']);
            } else {
                $parts[] = htmlspecialchars((string) $person[$item]);
            }
        }

        return implode(' ', $parts);
    }
}
