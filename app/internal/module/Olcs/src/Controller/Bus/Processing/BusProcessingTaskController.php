<?php

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller\Traits\LicenceNoteTrait;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends BusProcessingController
{

    protected $identifierName = 'id';
    protected $item = 'tasks';
    protected $service = 'Task';

    /**
     * Brings back a list of tasks based on the search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->getFromRoute('licence');
        $busReg = $this->getFromRoute('busRegId');
        $action = $this->getFromPost('action');

        $view = new \Zend\View\Model\ViewModel(['table'=>'']);
        $view->setTemplate('table');
        return $this->renderView($view);
    }
}
