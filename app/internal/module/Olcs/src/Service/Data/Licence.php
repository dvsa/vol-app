<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Dvsa\Olcs\Transfer\Query\Licence\GetList as GetLicenceListQry;

/**
 * Licence data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Licence extends AbstractDataService implements ListDataInterface
{
    const DEFAULT_ORDER = 'ASC';
    const DEFAULT_SORT = 'licNo';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'Licence';

    /**
     * @var int
     */
    protected $organisationId = null;

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
        $data = $this->fetchLicenceListData();

        if (!is_array($data)) {
            return [];
        }

        $ret = [];

        foreach ($data as $datum) {
            $ret[$datum['id']] = isset($datum['licNo']) ? $datum['licNo'] : $datum['id'];
        }

        return $ret;
    }

    /**
     * Fetch list data
     *
     * @return array
     */
    public function fetchLicenceListData()
    {
        if (is_null($this->getData('licenceList'))) {
            $dtoData = GetLicenceListQry::create(
                [
                    'sort'  => self::DEFAULT_SORT,
                    'order' => self::DEFAULT_ORDER,
                    'organisation' => $this->getOrganisationId(),
                    'excludeStatuses' => [],
                ]
            );
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                return [];

            } elseif (isset($response->getResult()['results'])) {
                $this->setData('licenceList', $response->getResult()['results']);
            }
        }

        return $this->getData('licenceList');
    }

    /**
     * Set organisation id
     *
     * @param int $organisationId organisation id
     *
     * @return void
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;
    }

    /**
     * Get organisation id
     *
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }
}
