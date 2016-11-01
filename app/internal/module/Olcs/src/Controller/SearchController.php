<?php

/**
 * Search controller
 * Search for operators and licences
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

/**
 * Main search controller
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SearchController extends AbstractController implements LeftViewProvider
{
    use \Common\Controller\Lva\Traits\CrudActionTrait;

    protected $navigationId = 'mainsearch';

    /**
     * At first glance this seems a little unnecessary, but we need to intercept the post
     * and turn it into a get. This way the search URL contains the search params.
     *
     * @return \Zend\Http\Response
     */
    public function postAction()
    {
        $sd = $this->ElasticSearch()->getSearchData();

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);
        // unset the page param, as when filtering or new search the pagination should be on page 1
        unset($sd['page']);

        return $this->redirect()->toRoute(
            'search',
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    /**
     * Back action
     *
     * @return \Zend\Http\Response
     */
    public function backAction()
    {
        $sd = $this->ElasticSearch()->getSearchData();

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);

        return $this->redirect()->toRoute(
            'search',
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    /**
     * Index action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->backAction();
    }

    /**
     * Search action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function searchAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->handleCrudAction($this->params()->fromPost());
        }

        /** @var \Common\Controller\Plugin\ElasticSearch $elasticSearch */
        $elasticSearch = $this->ElasticSearch();

        $this->loadScripts(['table-actions']);

        $form = $elasticSearch->getFiltersForm();
        $elasticSearch->processSearchData();

        $view = new ViewModel();
        $view->setTemplate('sections/search/pages/results');

        // make all elements not required
        foreach ($form->getInputFilter()->get('filter')->getInputs() as $input) {
            /* @var $input \Zend\InputFilter\Input */
            $input->setRequired(false);
        }

        // if valid then generate results
        if ($form->isValid()) {
            $elasticSearch->configureNavigation();
            $view = $elasticSearch->generateResults($view);
        }

        return $this->renderView($view, 'Search results');
    }

    /**
     * Reset action
     *
     * @return \Zend\Http\Response
     */
    public function resetAction()
    {
        /** @var \Common\Controller\Plugin\ElasticSearch $elasticSearch */
        $elasticSearch = $this->ElasticSearch();

        $sd = $elasticSearch->getSearchData();

        $elasticSearch->resetSearchSession($sd['search']);

        return $this->redirect()->toRoute(
            'search',
            ['index' => $sd['index'], 'action' => 'search'],
            ['query' => ['search' => $sd['search']], 'code' => 303],
            true
        );
    }

    /**
     * Get left view
     *
     * @return \Olcs\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/search/partials/left');

        return $view;
    }

    /**
     * Remove Vehicle Section 26 marker
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    protected function vehicleremove26Action()
    {
        if ($this->getRequest()->isPost()) {
            $ids = explode(',', $this->params('child_id'));
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Vehicle\UpdateSection26::create(
                    ['ids' => $ids, 'section26' => 'N']
                )
            );
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('form.vehicle.removeSection26.success');
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
            return $this->redirect()->toRouteAjax('search', array('index' => 'vehicle', 'action' => 'search'));
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $this->getRequest());
        $form->get('messages')->get('message')->setValue('form.vehicle.removeSection26.confirm');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Remove section 26');
    }

    /**
     * Set Vehicle Section 26 marker
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    protected function vehicleset26Action()
    {
        if ($this->getRequest()->isPost()) {
            $ids = explode(',', $this->params('child_id'));
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Vehicle\UpdateSection26::create(
                    ['ids' => $ids, 'section26' => 'Y']
                )
            );
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('form.vehicle.setSection26.success');
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
            return $this->redirect()->toRouteAjax('search', array('index' => 'vehicle', 'action' => 'search'));
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $this->getRequest());
        $form->get('messages')->get('message')->setValue('form.vehicle.setSection26.confirm');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Remove section 26');
    }

    /**
     * Process the search
     *
     * @param array $data Data
     *
     * @return \Zend\Http\Response
     */
    public function processSearch($data)
    {
        $data = array_merge($data['search'], $data['search-advanced']);

        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        /**
         * @NOTE (RC) added data to query string rather than route params as data contained a nested array which was
         * causing an error in zf2 url builder. I am informed by (CR) that this advanced search is disappearing soon
         * anyway
         */
        $url = $this->url()->fromRoute('operators/operators-params', [], array('query' => $data));

        return $this->redirect()->toUrl($url);
    }

    /**
     * Sets the navigation to that secified in the controller. Useful for when a controller is
     * 100% reresented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }
}
