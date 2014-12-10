<?php

/**
 * Bus Processing Note controller
 * Bus note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;

/**
 * BusProcessingRegistrationHistoryController controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class BusProcessingRegistrationHistoryController extends BusProcessingController implements CrudInterface
{

    protected $identifierName = 'busRegId';
    protected $service = 'BusReg';
    protected $tableName = '/bus/registration-history';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'properties' => 'ALL',
        'children' => [
            'busNoticePeriod' => [
                'properties' => 'ALL'
            ],
            'status' => [
                'properties' => 'ALL'
            ]
        ]
    ];


    /**
     * Loads list data by route number for the bus reg
     * @todo Remove this method once the route number has been set in the route via attached listener
     *
     * @param array $params
     * @return array
     */
    public function loadListData(array $params)
    {
        $listData = $this->getListData();

        if ($listData == null) {
            $params['sort'] = 'variationNo';
            $params['order'] = 'DESC';
            $data = $this->loadCurrent();
            $params['routeNo'] = $data['routeNo'];
            $listData = $this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle());

            $this->setListData($listData);
            $listData = $this->getListData();
        }

        return $listData;
    }
}
