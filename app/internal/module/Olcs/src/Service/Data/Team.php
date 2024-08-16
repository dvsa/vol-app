<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\ListDataInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Query\Team\TeamListData as TeamQry;

/**
 * Class Team
 *
 * @package Olcs\Service\Data
 */
class Team extends AbstractDataService implements ListDataInterface
{
    public const DEFAULT_ORDER = 'ASC';
    public const DEFAULT_SORT = 'name';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'Team';

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
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
                return [];
            } elseif (isset($response->getResult()['results'])) {
                $this->setData('teamlist', $response->getResult()['results']);
            }
        }

        return $this->getData('teamlist');
    }
}
