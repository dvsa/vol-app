<?php

/**
 * Licence Processing Tasks Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;

/**
 * Licence Processing Tasks Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingTasksController extends AbstractLicenceProcessingController
{
    use \Olcs\Controller\Traits\TaskSearchTrait;

    protected $section = 'tasks';

    public function indexAction()
    {
        $this->pageLayout = 'licence';

        $filters = $this->mapTaskFilters(
            array('licenceId' => $this->getFromRoute('licence'))
        );

        $table = $this->getTaskTable($filters, false);

        // the table's nearly all good except we don't want
        // a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $view = $this->getViewWithLicence(
            array(
                'table' => $table->render(),
                'form'  => $this->getTaskForm($filters),
                'inlineScript' => $this->loadScripts(['tasks'])
            )
        );

        $view->setTemplate('licence/processing');
        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }
}