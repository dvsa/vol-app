<?php
/**
 * History Controller
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent as MvcEvent;

// for type hints
use Olcs\View\Builder\BuilderInterface as ViewBuilderInterface;
use Olcs\Mvc\Controller\Plugin;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Common\Service\Cqrs\Response;

/**
 * Abstract class to extend for BASIC list/edit/delete functions
 *
 * @TODO generic methodology for adding scripts to actions
 * @TODO delete action
 * @TODO method to alter form depending on data retrieved
 * @TODO define post add/edit/delete redirect location as a parameter?
 * @TODO review navigation stuff...
 *
 * @method ViewBuilderInterface viewBuilder()
 * @method Plugin\Script script()
 * @method Plugin\Placeholder placeholder()
 * @method Plugin\Table table()
 * @method Response handleQuery(QueryInterface $query)
 * @method Response handleCommand(QueryInterface $query)
 */
abstract class AbstractInternalController extends AbstractActionController
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = '';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'partials/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = '';
    protected $listDto = '';
    protected $listVars = [];

    /**
     * Variables for controlling details view rendering
     * details view template and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = '';
    protected $itemParams = ['id'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = '';
    protected $updateCommand = '';
    protected $mapperClass = '';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = '';

    public function indexAction()
    {
        return $this->index(
            $this->listDto,
            $this->listVars,
            $this->defaultTableSortField,
            $this->tableViewPlaceholderName,
            $this->tableName,
            $this->tableViewTemplate
        );
    }

    public function detailsAction()
    {
        return $this->details(
            $this->itemDto,
            $this->itemParams,
            $this->detailsViewPlaceholderName,
            $this->detailsViewTemplate
        );
    }

    public function addAction()
    {
        return $this->add(
            $this->formClass,
            $this->createCommand,
            $this->mapperClass
        );
    }

    public function editAction()
    {
        return $this->edit(
            $this->formClass,
            $this->itemDto,
            $this->itemParams,
            $this->updateCommand,
            $this->mapperClass
        );
    }

    final protected function index(
        $listDto,
        $paramNames,
        $defaultSort,
        $tableViewPlaceholderName,
        $tableName,
        $tableViewTemplate
    ) {
        $listParams = $this->getListParams($paramNames, $defaultSort);
        $response = $this->handleQuery($listDto::create($listParams));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            $this->placeholder()->setPlaceholder(
                $tableViewPlaceholderName,
                $this->table()->buildTable($tableName, $data, $listParams)
            );
        }

        return $this->viewBuilder()->buildViewFromTemplate($tableViewTemplate);
    }

    final protected function details($itemDto, $paramNames, $detailsViewPlaceHolderName, $detailsViewTemplate)
    {
        $params = $this->getItemParams($paramNames);

        $response = $this->handleQuery($itemDto::create($params));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->placeholder()->setPlaceholder($detailsViewPlaceHolderName, $data);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($detailsViewTemplate);
    }

    final protected function add($formClass, $createCommand, $mapperClass)
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($formClass);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $commandData = $mapperClass::mapFromForm($form->getData());
                $response = $this->handleCommand($createCommand::create($commandData));

                if ($response->isServerError()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                }

                if ($response->isClientError()) {
                    $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());
                    foreach ($flashErrors as $error) {
                        $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                    }
                }

                if ($response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Created record');
                    return $this->redirectToIndex();
                }
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
    }

    final protected function edit($formClass, $itemDto, $paramNames, $updateCommand, $mapperClass)
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($formClass);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $commandData = $mapperClass::mapFromForm($form->getData());
                $response = $this->handleCommand($updateCommand::create($commandData));

                if ($response->isServerError()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                }

                if ($response->isClientError()) {
                    $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());
                    foreach ($flashErrors as $error) {
                        $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                    }
                }

                if ($response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Updated record');
                    return $this->redirectToIndex();
                }
            }
        } else {
            $itemParams = $this->getItemParams($paramNames);
            $response = $this->handleQuery($itemDto::create($itemParams));

            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $result = $response->getResult();
                $formData = $mapperClass::mapFromResult($result);
                $form->setData($formData);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
    }

    private function getListParams($paramNames, $defaultSort)
    {
        $params = [
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', $defaultSort),
            'order'   => $this->params()->fromQuery('order', 'DESC'),
            'limit'   => $this->params()->fromQuery('limit', 10),
        ];

        foreach ((array) $paramNames as $varName) {
            $params[$varName] = $this->params()->fromRoute($varName);
        }

        return $params;
    }

    private function getItemParams($paramNames)
    {
        $params = [];

        foreach ((array) $paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->params()->fromRoute($varName);
            } else {
                $params[$key] = $this->params()->fromRoute($varName);
            }
        }

        return $params;
    }

    protected function redirectToIndex()
    {
        return $this->redirect()->toRoute(
            null,
            ['action'=>'index'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * @codeCoverageIgnore this is part of the event system.
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        if (method_exists($this, 'setNavigationCurrentLocation')) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setNavigationCurrentLocation'), 6);
        }
    }

    /**
     * Sets the navigation to that specified in the controller. Useful for when a controller is
     * 100% represented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    final public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }
}
