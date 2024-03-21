<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Internal User data service with current user on top of the list
 *
 * @package Olcs\Service\Data
 */
class AssignedToList extends UserListInternal
{
    /**
     * Prepend current user, Not assigned and All options to list
     *
     * @param array $optionData The option data returned by formatData
     * @param bool  $useGroups  The 'use_groups' Form\Option
     *
     * @return array
     */
    private function prependOptions(array $optionData, $useGroups)
    {
        $currentUser = $this->getCurrentUser();

        $items = [
            $currentUser['id'] => $this->getPersonIdentifier($currentUser),
            'unassigned' => 'Not assigned',
            'all' => 'All'
        ];

        if ($useGroups) {
            $items = [[
                'label' => null,
                'options' => [
                    $currentUser['id'] => $this->getPersonIdentifier($currentUser),
                    'unassigned' => 'Not assigned',
                    'all' => 'All'
                ]
            ]];
        }

        $optionData = $items + $optionData;

        return $optionData;
    }

    /**
     * Get the current user details
     *
     * @return array
     */
    private function getCurrentUser()
    {
        $response = $this->handleQuery(
            TransferQry\MyAccount\MyAccount::create([])
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        return $response->getResult();
    }

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
        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        if ($useGroups) {
            return $this->prependOptions($this->formatDataForGroups($data), $useGroups);
        }

        return $this->prependOptions($this->formatData($data), $useGroups);
    }
}
