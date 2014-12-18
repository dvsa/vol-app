<?php

/**
 * Licence Processing Tasks Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;
use \Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Licence Processing Tasks Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceProcessingTasksController extends AbstractLicenceProcessingController
{
    /**
     * @var string
     */
    protected $section = 'tasks';

    public function indexAction()
    {
        $redirect = $this->processTasksActions('licence');
        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters(
            [
                'licenceId'      => $this->getFromRoute('licence'),
                'assignedToTeam' => '',
                'assignedToUser' => ''
            ]
        );

        $table = $this->getTaskTable($filters, false);

        // the table's nearly all good except we don't want a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions']);

        $view = new ViewModel(['table' => $table->render()]);

        $view->setTemplate('table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }
}
