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
abstract class CrudAbstract extends CommonController\AbstractSectionController implements CommonController\CrudInterface
{
    use Traits\DeleteActionTrait;

    protected $requiredProperties = [
        'formName',
        'identifierName',
        'tableName',
        'dataMap',
        'dataBundle',
        'service',
        'pageLayout',
        'listVars',
        //'detailsView'
    ];

    protected $pageLayoutInner = null;

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [];

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkRequiredProperties'), 5);

        if (method_exists($this, 'setNavigationCurrentLocation')) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setNavigationCurrentLocation'), 6);
        }
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
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

    /**
     * Index Action.
     */
    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        $this->buildCommentsBoxIntoView();

        $view->setTemplate('crud/index');

        return $this->renderView($view);
    }

    /**
     * Returns the listVars property.
     *
     * @return array
     */
    public function getListVars()
    {
        return $this->listVars;
    }

    /**
     * Builds table into its placeholder.
     *
     * @return void
     */
    public function buildTableIntoView()
    {
        $params = $this->getTableParams();

        $data = $this->loadListData($params);

        $data = $this->preProcessTableData($data);

        $this->getViewHelperManager()->get('placeholder')->getContainer('table')->set(
            $this->alterTable($this->getTable($this->getTableName(), $data, $params))
        );
    }

    public function loadListData(array $params)
    {
        return $this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle());
    }

    public function getTableParams()
    {
        $params = [
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'id'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $listVars = $this->getListVars();
        for($i=0; $i<count($listVars); $i++) {
            $params[$listVars[$i]] = $this->getQueryOrRouteParam($listVars[$i], null);
        }

        return $params;
    }

    /**
     * Pre processes the data before it's injected into the table.
     *
     * @param array $data
     * @return array
     */
    public function preProcessTableData($data)
    {
        return $data;
    }

    /**
     * Master details option.
     */
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
     * Sets the view helper placeholder namespaced value.
     *
     * @param string $namespace
     * @param mixed $content
     */
    public function setPlaceholder($namespace, $content)
    {
        $this->getViewHelperManager()->get('placeholder')
             ->getContainer($namespace)->set($content);
    }

    public function buildCommentsBoxIntoView()
    {
        return null;
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

    /**
     * Simple redirect to details action.
     *
     * @return \Zend\Http\Response
     */
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

        $this->setPlaceholder('form', $form);

        $view->setTemplate('crud/form');

        return $this->renderView($view);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        $result = parent::processSave($data);

        $this->addSuccessMessage('Saved sucessfully');

        if (func_num_args() > 1 && func_get_arg(1) === false /* redirect = false */) {
            return $result;
        }

        return $this->redirectToIndex();
    }

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            null,
            ['action'=>'index', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Method checks that the required properties exist.
     *
     * @throws \LogicException
     * @return boolean
     */
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

            //die('<pre>' . print_r((array)$view->getVariables(), 1));

            // This is a zend\view\variables object - cast it to an array.
            $layout = $this->getView((array)$view->getVariables());

            $layout->setTemplate($this->pageLayoutInner);

            $this->maybeAddScripts($layout);

            $layout->addChild($view, 'content');

            return parent::renderView($layout, $pageTitle, $pageSubTitle);
        }

        return parent::renderView($view, $pageTitle, $pageSubTitle);
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

    /**
     * Sets the navigation Id. Usually, this would be set as the default
     * value in a child controller, however, we need to re set it in places.
     *
     * @param unknown $navigationId
     * @return \Olcs\Controller\CrudAbstract
     */
    public function setNavigationId($navigationId)
    {
        $this->navigationId = $navigationId;
        return $this;
    }

    /**
     * Gets a variable from the route
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromRoute($param, $default = null)
    {
        return $this->getFromRoute($param, $default);
    }

    /**
     * Gets a variable from postdata
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromPost($param, $default = null)
    {
        return $this->getFromPost($param, $default);
    }

    /**
     * Replaces arrays with Ids, just with the value of the ID.
     *
     * @param array $array
     * @param array $ids
     *
     * @return array
     */
    public function replaceIds(array $array, array $ids)
    {
        foreach ($array as $key => $value) {
            if (in_array($key, $ids)) {
                $array[$key] = $value['id'];
            }
        }

        return $array;
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['id'])) {
            if (isset($this->getDataBundle()['children'])) {

                $fields = array_keys($this->getDataBundle()['children']);
                $data = $this->replaceIds($data, $fields);
            }
            $data['fields'] = $data;
            $data['base'] = $data;
        } else {
            $data = [];
            $data['case'] = $this->getQueryOrRouteParam('case');
            $data['fields']['case'] = $this->getQueryOrRouteParam('case');
            $data['base']['case'] = $this->getQueryOrRouteParam('case');
        }

        return $data;
    }
}