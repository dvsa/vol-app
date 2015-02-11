<?php

/**
 * Bus Processing Note controller
 * Bus note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;
use Zend\View\Model\ViewModel;

/**
 * BusProcessingRegistrationHistoryController controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class BusProcessingRegistrationHistoryController extends BusProcessingController implements CrudInterface
{

    protected $identifierName = 'busRegId';
    protected $service = 'BusReg';
    protected $tableName = 'Bus/registration-history';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array('table-actions');

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
     * Performs a delete action and redirects to the appropriate place
     */
    public function deleteAction()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);

        $response = $this->confirm(
            'Are you sure you want to permanently delete this record?'
        );

        //echo get_class($response); die();

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $data = $this->loadCurrent();

        $this->makeRestCall($this->getDeleteServiceName(), 'DELETE', ['id' => $id]);

        $this->addErrorMessage('Deleted successfully');

        if ($data['variationNo'] > 0) {
            $params = [
                'sort' => 'variationNo',
                'order' => 'DESC',
                'limit' => 1,
                'routeNo' => $data['routeNo']
            ];

            $listData = $this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle());

            if (isset($listData['Results'][0])) {
                return $this->redirect()->toRouteAjax(
                    null,
                    ['action'=>'index', $this->getIdentifierName() => $listData['Results'][0]['id']],
                    ['code' => '303'], // Why? No cache is set with a 303 :)
                    true
                );
            }
        }

        //no other variation is available so redirect back to licence bus page
        return $this->redirect()->toRouteAjax(
            'licence/bus',
            ['action'=>'bus', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

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

        $this->identifierName = 'busRegId';

        if ($listData == null) {
            $params['sort'] = 'variationNo';
            $params['order'] = 'DESC';
            $data = $this->loadCurrent();
            $params['routeNo'] = $data['routeNo'];
            $listData = $this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle());

            $this->setListData($listData);
            $listData = $this->getListData();
        }

        if (isset($listData['Results'][0])) {
            $listData['Results'][0]['canDelete'] = true;
        }

        return $listData;
    }

    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action'=>'index',],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
