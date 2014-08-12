<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class IndexController extends FormActionController
{
    protected $enableCsrf = false;

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
        $filters = $this->filterRequest();

        // we want to keep $search and $filters separate
        // as we'll use filters again later to populate
        // the form
        $search = array_merge(
            $filters,
            array(
                'userId' => $this->getLoggedInUser()
            )
        );

        $tasks = $this->makeRestCall(
            'Task',
            'GET',
            $search
        );

        $table = $this->buildTable('tasks', $tasks);

        $form = $this->generateFormWithData(
            'tasks-home', null, $filters
        );

        $selects = array(
            'team' => $this->getListData('Team'),
            'owner' => $this->getListData('User'),
            'category' => $this->getListData('Category'),
            'sub_category' => $this->getListData('TaskSubCategory')
        );

        foreach ($selects as $name => $options) {
            $form->get($name)
                ->setValueOptions($options)
                ->setEmptyOption(null);
        }

        $form->get('date')->setValue('today');
        $form->get('status')->setValue('open');

        $view = new ViewModel();
        $view->setVariables(
            array(
                'table' => $table,
                'form'  => $form,
                'inlineScript' => $this->getServiceLocator()->get('Script')->loadFiles(['tasks'])
            )
        );

        $view->setTemplate('index/home');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());
        return $view;
    }

    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function filterRequest()
    {
        return $this->getRequest()->getQuery()->toArray();
    }

    protected function getListData($entity, $data = array(), $primaryKey = 'id', $titleKey = 'name')
    {
        $response = $this->makeRestCall($entity, 'GET', $data);

        $final = array();
        foreach ($response['Results'] as $result) {
            $key = $result[$primaryKey];
            $value = $result[$titleKey];

            $final[$key] = $value;
        }
        return $final;
    }

    public function taskFilterAction()
    {
        $map = array(
            'users' => array(
                'entity' => 'User',
                'field' => 'team_id'
            ),
            'sub-categories' => array(
                'entity' => 'TaskSubCategory',
                'field' => 'category_id'
            )
        );

        $lookup = $map[$this->params()->fromRoute('type')];
        $search = array(
            $lookup['field'] => $this->params()->fromRoute('value')
        );

        $results = $this->getListData($lookup['entity'], $search);
        $viewResults = array();

        foreach ($results as $id => $result) {
            $viewResults[] = array(
                'value' => $id,
                'label' => $result
            );
        }

        return new JsonModel($viewResults);
    }

    public function makeRestCall($entity, $method, array $options, array $bundle = null)
    {
        // @TODO kill this filth, obviously
        switch ($entity) {
        case 'Team':
            $data = array(
                array(
                    'id' => 'all',
                    'name' => 'All',
                ),
                array(
                    'id' => '1',
                    'name' => 'A Team',
                ),
                array(
                    'id' => '2',
                    'name' => 'B Team',
                )
            );
            break;
        case 'User':
            $data = array(
                array(
                    'id' => 'all',
                    'name' => 'All',
                ),
                array(
                    'id' => '1',
                    'name' => 'A User',
                ),
                array(
                    'id' => '2',
                    'name' => 'B User',
                )
            );
            break;
        case 'Category':
            $data = array(
                array(
                    'id' => 'all',
                    'name' => 'All',
                ),
                array(
                    'id' => '1',
                    'name' => 'A Category',
                ),
                array(
                    'id' => '2',
                    'name' => 'B Category',
                )
            );
            break;
        case 'TaskSubCategory':
            $data = array(
                array(
                    'id' => 'all',
                    'name' => 'All',
                ),
                array(
                    'id' => '1',
                    'name' => 'A Sub Category',
                ),
                array(
                    'id' => '2',
                    'name' => 'B Sub Category',
                )
            );
            break;
        default:
            return parent::makeRestCall($entity, $method, $options);
        }

        return array(
            'Results' => $data,
            'Count'   => count($data)
        );
    }
}
