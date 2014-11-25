<?php

/**
 * Application Processing Tasks Controller
 */
namespace Olcs\Controller\Application\Processing;

use Zend\View\Model\ViewModel;
use \Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Application Processing Tasks Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksController extends AbstractApplicationProcessingController
{
    use TaskSearchTrait;

    protected $headerViewTemplate = 'application/header';

    /**
     * @var string
     */
    protected $section = 'tasks';

    public function indexAction()
    {
        $redirect = $this->processTasksActions('application');
        if ($redirect) {
            return $redirect;
        }

        // we want all tasks related to the licence, not just this application
        $applicationId = $this->params('application');
        $licenceId = $this->getServiceLocator()->get('Entity\Application')->getLicenceIdForApplication($applicationId);
        $filters = $this->mapTaskFilters(
            array(
                'licenceId'      => $licenceId,
                'assignedToTeam' => '',
                'assignedToUser' => ''
            )
        );

        $table = $this->getTaskTable($filters, false);

        // the table's nearly all good except we don't want a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions']);

        $view = new ViewModel(['table' => $table->render()]);

        $view->setTemplate('application/processing/layout');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }
}
