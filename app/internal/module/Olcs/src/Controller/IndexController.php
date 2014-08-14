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
    protected $pageTitle = 'Home';
    protected $pageSubTitle = 'Subtitle';

    public function indexAction()
    {
        $filters = $this->mapRequest();

        $tasks = $this->makeRestCall(
            'TaskSearchView',
            'GET',
            $filters
        );

        $table = $this->buildTable('tasks', $tasks);

        $form = $this->getForm('tasks-home');

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = array(
            'team' => $this->getListData('Team'),
            'owner' => $this->getListData('User'),
            'category' => $this->getListData('Category', [], 'id', 'description'),
            'subCategory' => $this->getListData('TaskSubCategory')
        );

        // bang the relevant data into the corresponding form inputs
        foreach ($selects as $name => $options) {
            $form->get($name)
                ->setValueOptions($options);
        }

        $form->setData($filters);

        $view = new ViewModel(
            array(
                'table' => $table,
                'form'  => $form,
                'inlineScript' => $this->loadScripts(['tasks'])
            )
        );
        $view->setTemplate('index/home');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Retrieve a list of entities, filtered by a certain key.
     * The consumer doesn't control what the entities and keys are; they
     * simply provide a key and a value which we look up in a map
     *
     * @return JSON
     */
    public function taskFilterAction()
    {
        $key = $this->params()->fromRoute('type');
        $value = $this->params()->fromRoute('value');
        $map = array(
            'users' => array(
                'entity' => 'User',
                'field' => 'team'
            ),
            'sub-categories' => array(
                'entity' => 'TaskSubCategory',
                'field' => 'category'
            )
        );

        if (!isset($map[$key])) {
            // @TODO handle separately?
            throw new \Exception("Invalid task filter key: " . $key);
        }

        $lookup = $map[$key];

        // e.g. array("category_id" => 12)
        $search = array(
            $lookup['field'] => $value
        );

        $results = $this->getListData($lookup['entity'], $search);
        $viewResults = array();

        // iterate over the list data and just convert it to a more
        // JS friendly format (key/val assoc isn't quite such a neat
        // fit for frontend)
        foreach ($results as $id => $result) {
            $viewResults[] = array(
                'value' => $id,
                'label' => $result
            );
        }

        return new JsonModel($viewResults);
    }


    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function mapRequest()
    {
        $defaults = array(
            'owner'  => $this->getLoggedInUser(),
            'team'   => 2,  // we've no stub for this, but it matches the logged in user's team
            'date'   => 'today',
            'status' => 'open',
        );

        $filters = array_merge(
            $defaults,
            $this->getRequest()->getQuery()->toArray()
        );

        // map form => backend keys

        $filters['isClosed'] = $filters['status'] === 'closed';
        $filters['isUrgent'] = isset($filters['urgent']);

        return array_filter(
            $filters,
            function ($v) {
                return !empty($v);
            }
        );
    }

    /**
     * Retrieve some data from the backend and convert it for use in
     * a select. Optionally provide some search data to filter the
     * returned data too.
     */
    protected function getListData($entity, $data = array(), $primaryKey = 'id', $titleKey = 'name')
    {
        $data['limit'] = 100;
        $response = $this->makeRestCall($entity, 'GET', $data);

        $final = array('' => 'All');
        foreach ($response['Results'] as $result) {
            $key = $result[$primaryKey];
            $value = $result[$titleKey];

            $final[$key] = $value;
        }
        return $final;
    }
}
