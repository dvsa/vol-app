<?php

/**
 * Task Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits;

/**
 * Task Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaskController extends AbstractController
{
    use Traits\LicenceControllerTrait;

    /**
     * Add a new task
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $data = $this->mapDefaultData();
        $filters = $this->mapFilters($data);

        $form = $this->getForm('task');

        $selects = array(
            'details' => array(
                'category' => $this->getListData('Category', [], 'description'),
                'taskSubCategory' => $this->getListData('TaskSubCategory', $filters)
            ),
            'assignment' => array(
                'assignedToTeam' => $this->getListData('Team'),
                'assignedToUser' => $this->getListData('User', $filters, 'name', 'id', 'Unassigned')
            )
        );

        foreach ($selects as $fieldset => $inputs) {
            foreach ($inputs as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

        $licence = $this->getLicence();

        $url = sprintf(
            '<a href="%s">%s</a>',
            $this->url()->fromRoute(
                'licence',
                array(
                    'licence' => $this->getFromRoute('licence')
                )
            ),
            $licence['licNo']
        );

        $details = $form->get('details');

        $details->get('link')->setValue($url);
        $details->get('status')->setValue('<b>Open</b>');

        $form->setData(['assignment' => $data]);

        $this->formPost($form, 'processAddTask');

        $view = new ViewModel(
            [
                'form' => $form,
                'inlineScript' => $this->loadScripts(['task-form'])
            ]
        );
        $view->setTemplate('task/add');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());
        return $this->renderView($view, 'Add task');
    }

    /**
     * Override the parent getListData method simply to save us constantly having to
     * supply the $showAll parameter as 'Please select'
     */
    protected function getListData($entity, $data = array(), $titleKey = 'name', $primaryKey = 'id', $showAll = 'Please select')
    {
        return parent::getListData($entity, $data, $titleKey, $primaryKey, $showAll);
    }

    /**
     * Callback invoked when the form is valid
     */
    public function processAddTask($data)
    {
        $licence = $this->getFromRoute('licence');

        $data = $this->flattenData($data);

        $data['licence'] = $licence;

        $data['urgent'] = $data['urgent'] == '1' ? 'Y' : 'N';

        $result = $this->processAdd($data, 'Task');

        if (isset($result['id'])) {
            $this->redirect()->toRoute(
                'licence/processing',
                array('licence' => $licence)
            );
        }
    }

    /**
     * Merge some sensible default dropdown values
     * with any POST data we may have
     */
    private function mapDefaultData()
    {
        $defaults = [
            'assignedToUser' => $this->getLoggedInUser(),
            'assignedToTeam' => 2
        ];

        $data = $this->flattenData(
            $this->getRequest()->getPost()->toArray()
        );

        return array_merge(
            $defaults,
            $data
        );
    }

    /**
     * Map some flattened data into relevant dropdown
     * filters
     */
    private function mapFilters($data)
    {
        $filters = [];

        if (!empty($data['assignedToTeam'])) {
            $filters['team'] = $data['assignedToTeam'];
        }
        if (!empty($data['category'])) {
            $filters['category'] = $data['category'];
        }

        return $filters;
    }

    /**
     * Flatten nested fieldset data into a collapsed
     * array
     */
    private function flattenData($data)
    {
        if (empty($data)) {
            return [];
        }
        return array_merge(
            $data['details'],
            $data['assignment']
        );
    }
}
