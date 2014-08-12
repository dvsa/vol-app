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

        // @TODO: these all need to come from the backend
        $selects = array(
            'team' => array(
                '1' => 'Team A',
                '2' => 'Team B',
                '3' => 'Team C'
            ),
            'owner' => array(),
            'category' => array(
                'A' => 'Category A',
                'B' => 'Category B',
                'C' => 'Category C'
            ),
            'sub_category' => array()
        );

        foreach ($selects as $name => $options) {
            $options = array_merge(
                array('all' => 'All'),
                $options
            );
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

    public function taskFilterAction()
    {
        return new JsonModel(
            array(
                array(
                    'value' => 'all',
                    'label' => 'All'
                ),
                array(
                    'value' => 'foo',
                    'label' => 'Foo'
                ),
                array(
                    'value' => 'bar',
                    'label' => 'Bar'
                )
            )
        );
    }
}
