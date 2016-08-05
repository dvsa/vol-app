<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Dvsa\Olcs\Transfer\Query\Team\TeamListData as TeamQry;

/**
 * Class Team
 * @package Olcs\Service
 */
class Team extends AbstractDataService implements ListDataInterface
{
    const DEFAULT_ORDER = 'ASC';
    const DEFAULT_SORT = 'name';

    protected $id;
    protected $serviceName = 'Team';

    /**
     * Fetch list options
     *
     * @param mixed $context   Context
     * @param bool  $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchTeamListData();

        if (!is_array($data)) {
            return [];
        }

        $ret = [];
        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum['name'];
        }

        return $ret;
    }

    /**
     * Fetch list data
     *
     * @return array
     */
    public function fetchTeamListData()
    {
        if (is_null($this->getData('teamlist'))) {
            $dtoData = TeamQry::create(
                [
                    'sort'  => self::DEFAULT_SORT,
                    'order' => self::DEFAULT_ORDER
                ]
            );
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                return [];

            } elseif (isset($response->getResult()['results'])) {
                $this->setData('teamlist', $response->getResult()['results']);
            }
        }

        return $this->getData('teamlist');
    }
}
