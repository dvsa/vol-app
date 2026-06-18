<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\User\RoleList;

/**
 * Class Role
 *
 * @package Common\Service\Data
 */
class Role extends AbstractDataService implements ListData
{
    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        $optionData = [];
        $data = $this->fetchListData();

        foreach ($data as $datum) {
            $optionData[$datum['role']] = $datum['description'];
        }

        return $optionData;
    }

    /**
     * Fetch list data
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData()
    {
        if (is_null($this->getData('Role'))) {
            $this->setData('Role', false);
            $dtoData = RoleList::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('Role', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('Role', $response->getResult()['results']);
            }
        }

        return $this->getData('Role');
    }
}
