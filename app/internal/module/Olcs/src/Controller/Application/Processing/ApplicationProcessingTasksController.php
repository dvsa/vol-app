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

    protected $section = 'tasks';

    public function indexAction()
    {
        $redirect = $this->processTasksActions('application');
        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters(
            array(
                'linkId' => $this->getFromRoute('lva-application'),
                'linkType' => 'Application',
                'assignedToTeam' => '',
                'assignedToUser' => ''
            )
        );

        $table = $this->getTaskTable($filters, false);

        // the table's nearly all good except we don't want
        // a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions']);

        $view = new ViewModel(
            array(
                'table' => $table->render()
            )
        );

        $view->setTemplate('application/processing/layout');
        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }
}
