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
        $licence = $this->getLicence();

        $form = $this->getForm('task');

        $selects = array(
            'details' => array(
                'category' => $this->getListData('Category', [], 'description'),
                'taskSubCategory' => $this->getListData('TaskSubCategory')
            ),
            'assignment' => array(
                'assignedToTeam' => $this->getListData('Team'),
                'assignedToUser' => $this->getListData('User', [], 'name', 'id', 'Unassigned'),
            )
        );

        foreach ($selects as $fieldset => $data) {
            foreach ($data as $name => $options) {
                $form->get($fieldset)
                    ->get($name)
                    ->setValueOptions($options);
            }
        }

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
        $form->get('details')->get('link')->setValue($url);

        // really not sure about this...
        $form->get('details')->get('status')->setValue('<b>Open</b>');

        $this->formPost($form, 'processAddTask');

        $view = new ViewModel(
            [
                'form' => $form,
                'inlineScript' => $this->loadScripts(['tasks'])
            ]
        );
        $view->setTemplate('task/add');
        return $this->renderView($view, 'Add task');
    }

    protected function getListData($entity, $data = array(), $titleKey = 'name', $primaryKey = 'id', $showAll = 'Please select')
    {
        return parent::getListData($entity, $data, $titleKey, $primaryKey, $showAll);
    }

    public function processAddTask($data)
    {
        $licence = $this->getFromRoute('licence');

        $data = array_merge(
            $data['details'],
            $data['assignment']
        );

        $data['licence'] = $licence;

        $data['urgent'] = isset($data['urgent']) ? 'Y' : 'N';

        $result = $this->processAdd($data, 'Task');

        if (isset($result['id'])) {
            $this->redirect()->toRoute(
                'licence/processing',
                array('licence' => $licence)
            );
        }
    }
}
