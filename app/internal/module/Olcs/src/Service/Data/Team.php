<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dvsa\Olcs\Transfer\Query\Team\TeamList as TeamQry;

/**
 * Class Team
 * @package Olcs\Service
 */
class Team extends AbstractData implements ListDataInterface, ServiceLocatorAwareInterface
{
    // @todo: move ServiceLocator to AbstractData during the Data services migration process
    use ServiceLocatorAwareTrait;

    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 100;
    const DEFAULT_ORDER = 'ASC';
    const DEFAULT_SORT = 'id';

    protected $id;
    protected $serviceName = 'Team';

    /**
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchTeamListData();
        $ret = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum['name'];
        }

        return $ret;
    }

    public function fetchTeamListData()
    {
        if (is_null($this->getData('teamlist'))) {

            $params = [
                'page'  => self::DEFAULT_PAGE,
                'limit' => self::DEFAULT_LIMIT,
                'sort'  => self::DEFAULT_SORT,
                'order' => self::DEFAULT_ORDER
            ];
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    TeamQry::create($params)
                );

            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            $result = [];
            if ($response->isOk()) {
                $result = $response->getResult();
            }

            $this->setData('teamlist', $result['results']);
        }

        return $this->getData('teamlist');
    }
}
