<?php

namespace Olcs\Service\Data;

/**
 * Internal User data service
 *
 * @package Olcs\Service\Data
 */
class UserListInternalExcludingLimitedReadOnlyUsers extends UserListInternal
{
    const ROLE_INTERNAL_LIMITED_READ_ONLY = 'internal-limited-read-only';

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $list = $this->fetchListData($context);

        if (!is_array($list)) {
            return [];
        }

        foreach ($list as $key => $user) {
            foreach ($user['roles'] as $role) {
                if ($role['role'] === self::ROLE_INTERNAL_LIMITED_READ_ONLY) {
                    unset($list[$key]);
                }
            }
        }

        if ($useGroups) {
            return $this->formatDataForGroups($list);
        }

        return $this->formatData($list);
    }
}
