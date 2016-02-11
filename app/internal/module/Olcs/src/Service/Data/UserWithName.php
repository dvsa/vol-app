<?php

/**
 * User data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

/**
 * User data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UserWithName extends User
{
    /**
     * @param mixed $context
     * @param bool $useGroups
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
            if (isset($datum['contactDetails']['person']['forename']) &&
                $datum['contactDetails']['person']['familyName']) {
                $ret[$datum['id']] = $datum['contactDetails']['person']['forename'] . ' ' .
                    $datum['contactDetails']['person']['familyName'];
            } else {
                $ret[$datum['id']] = $datum['loginId'];
            }
        }

        return $ret;
    }
}
