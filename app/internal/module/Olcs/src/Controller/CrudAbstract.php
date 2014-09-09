<?php
/**
 * Crud Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech>
 */
namespace Olcs\Controller;

use Common\Controller as CommonController;
use Olcs\Controller\Traits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * Crud Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech>
 */
class CrudAbstract extends CommonController\AbstractSectionController implements CommonController\CrudInterface
{
    use Traits\DeleteActionTrait;

    protected $requiredProperties = [
        'formName',
        'identifierName',
        'dataMap',
        'dataBundle',
        'service',
        'pageLayout',
        'listVars',
        //'detailsView'
    ];

    protected $pageLayoutInner = null;

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkRequiredProperties'), 5);
    }

    public function getQueryOrRouteParam($name, $default = null)
    {
        if ($queryValue = $this->params()->fromQuery($name, $default)) {
            return $queryValue;
        }

        if ($queryValue = $this->params()->fromRoute($name, $default)) {
            return $queryValue;
        }

        return $default;
    }

    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $params = [
            //'licence' => $this->params()->fromRoute('licence'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'id'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        for($i=0; $i<count($this->listVars); $i++) {
            $params[$this->listVars[$i]] = $this->getQueryOrRouteParam($this->listVars[$i]);
        }

        $results = $this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle());

        //die('<pre>' . print_r($results, 1));

        // CR: This should be improved by makeing the table itself a view helper - which it should be!
        $this->getViewHelperManager()->get('placeholder')->getContainer('table')->set(
            $this->buildTable($this->getIdentifierName(), $results, $params)
        );

        $view->setTemplate('crud/index');

        return $this->renderView($view);
    }

    public function detailsAction()
    {
        $view = $this->getView([]);

        $this->getViewHelperManager()
             ->get('placeholder')
             ->getContainer($this->getIdentifierName())
             ->set($this->loadCurrent());

        $view->setTemplate($this->detailsView);

        return $this->renderView($view);
    }

    /**
     * Proxy method for save.
     *
     * @see saveThis()
     */
    public function addAction()
    {
        return $this->saveThis();
    }

    /**
     * Proxy method for save.
     *
     * @see saveThis()
     */
    public function editAction()
    {
        return $this->saveThis();
    }

    public function redirectAction()
    {
        return $this->redirect()->toRoute(
            null,
            array('action' => 'index', $this->getIdentifierName() => $this->getIdentifier()),
            true
        );
    }

    /**
     * Responsible for saving the posted data.
     */
    public function saveThis()
    {
        $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $this->getDataForForm());

        $view = $this->getView();

        // CR: Should be its own view helper - I'll refactor this later.
        $this->getViewHelperManager()->get('placeholder')->getContainer('form')->set($form);

        $view->setTemplate('crud/form');

        return $this->renderView($view);
    }

    public function checkRequiredProperties()
    {
        $missingProperties = false;

        $classProperties = array_keys(get_object_vars($this));

        foreach ($this->requiredProperties as $requiredProperty) {

            if (!in_array($requiredProperty, $classProperties) || empty($this->{$requiredProperty})) {

                $missingProperties[] = $requiredProperty;
            }
        }

        if ($missingProperties) {

            $message = 'Missing properties: ' . implode(', ', $missingProperties) . PHP_EOL;

            $message .= 'These properties are set: ' . implode(', ', $classProperties) . PHP_EOL;

            throw new \LogicException($message, null, null);
        }

        return true;
    }

    /**
     * Really useful method that gets us the view helper manager
     * from the service locator.
     *
     * @return ViewHelperManager
     */
    public function getViewHelperManager()
    {
        return $this->getServiceLocator()->get('viewHelperManager');
    }

    /**
     * Extend the render view method
     *
     * @param type $view
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        if (!is_null($this->pageLayoutInner)) {

            $layout = $this->getView($view->getVariables());

            $layout->setTemplate($this->pageLayoutInner);

            $this->maybeAddScripts($layout);

            $layout->addChild($view, 'content');

            return parent::renderView($layout, $pageTitle, $pageSubTitle);
        }

        return parent::renderView($view, $pageTitle, $pageSubTitle);
    }
}