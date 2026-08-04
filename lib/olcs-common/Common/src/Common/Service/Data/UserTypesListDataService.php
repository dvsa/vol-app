<?php

namespace Common\Service\Data;

/**
 * Class UserTypesListDataService
 * Provides list options for user types
 *
 * @package Olcs\Service\Data
 */
class UserTypesListDataService implements ListDataInterface
{
    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function fetchListOptions($context = null, $useGroups = false)
    {
        return [
            'internal' => 'Internal',
            'local-authority' => 'Local authority',
            'operator' => 'Operator',
            'partner' => 'Partner',
            'transport-manager' => 'Transport Manager / Operator with TM access',
        ];
    }
}
