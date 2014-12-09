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

    protected $identifierName = 'id';
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

    public function indexAction()
    {
        $view = $this->getViewWithBusReg();

        $view->setTemplate('licence/bus/index');

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        $this->buildCommentsBoxIntoView();

        $view->setTemplate('crud/index');

        return $this->renderView($view);

    }
}
