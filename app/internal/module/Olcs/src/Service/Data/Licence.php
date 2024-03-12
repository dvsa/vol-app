<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\ListDataInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Query\Licence\GetList as GetLicenceListQry;

/**
 * Licence data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Licence extends AbstractDataService implements ListDataInterface
{
    public const DEFAULT_ORDER = 'ASC';
    public const DEFAULT_SORT = 'licNo';

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

    /** @var FlashMessengerHelperService */
    protected $flashMessengerHelper;

    /**
     * Create service instance
     *
     * @param AbstractDataServiceServices $abstractDataServiceServices
     * @param FlashMessengerHelperService $flashMessengerHelper
     *
     * @return Team
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->flashMessengerHelper = $flashMessengerHelper;
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchLicenceListData();

        if (!is_array($data)) {
            return [];
        }

        $ret = [];

        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum['licNo'] ?? $datum['id'];
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
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
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
