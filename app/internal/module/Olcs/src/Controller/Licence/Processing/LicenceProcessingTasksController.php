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
        if ($this->getRequest()->isPost()) {
            $action = strtolower($this->params()->fromPost('action'));
            if ($action === 'create task') {
                $action = 'add';
            }

            $params = [
                'licence' => $this->getFromRoute('licence'),
                'action'  => $action
            ];

            if ($action !== 'add') {
                $id = $this->params()->fromPost('id');

                // @NOTE: edit doesn't allow multi IDs, but other
                // actions (like reassign) might, hence why we have
                // an explicit check here
                if ($action === 'edit') {
                    if (!is_array($id) || count($id) !== 1) {
                        throw new \Exception('Please select a single task to edit');
                    }
                    $id = $id[0];
                }

                $params['task'] = $id;
            }

            return $this->redirect()->toRoute(
                'licence/task_action',
                $params
            );
        }

        $this->pageLayout = 'licence';

        $filters = $this->mapTaskFilters(
            array('linkId' => $this->getFromRoute('licence'), 'linkType' => 'Licence')
        );
        
        $table = $this->getTaskTable($filters, false);

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
