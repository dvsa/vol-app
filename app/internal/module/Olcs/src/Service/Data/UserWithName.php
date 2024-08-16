<?php

namespace Olcs\Service\Data;

/**
 * User data service
 *
 * @package Olcs\Service\Data
 */
class UserWithName extends User
{
    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchUserListData();
        $ret = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $datum) {
            if (
                isset($datum['contactDetails']['person']['forename']) &&
                $datum['contactDetails']['person']['familyName']
            ) {
                $ret[$datum['id']] = $datum['contactDetails']['person']['forename'] . ' ' .
                    $datum['contactDetails']['person']['familyName'];
            } else {
                $ret[$datum['id']] = $datum['loginId'];
            }
        }

        return $ret;
    }
}
