<?php

/**
 * Operator Irfo Psv Authorisations Controller
 */
namespace Olcs\Controller\Operator;

use Common\Form\Elements\Types\Html;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPsvAuth as CreateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\GrantIrfoPsvAuth as GrantDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuth as ItemDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\IrfoPsvAuth as Mapper;
use Olcs\Form\Model\Form\IrfoPsvAuth as Form;
use Zend\Form\Element\Hidden;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Zend\Form\Form as ZendForm;
use Common\Form\Elements\InputFilters\ActionButton;
use Olcs\Logging\Log\Logger;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

/**
 * Operator Irfo Psv Authorisations Controller
 */
class OperatorIrfoPsvAuthorisationsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'operator_irfo_psv_authorisations';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/irfo-psv-auth-numbers', 'forms/irfo-psv-auth-copies'],
        'editAction' => ['forms/irfo-psv-auth-numbers', 'forms/irfo-psv-auth-copies'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $tableName = 'operator.irfo.psv-authorisations';
    protected $listDto = ListDto::class;
    protected $listVars = ['organisation'];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add IRFO PSV Authorisation';
    protected $editContentTitle = 'Edit IRFO PSV Authorisation';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'organisation' => 'route',
        'status' => 'irfo_auth_s_pending',
    ];

    private function determineResponse($commandData)
    {
        switch ($commandData['action'])
        {
            case 'grant':
                return $this->handleCommand(GrantDto::create($commandData));
            default:
                return $this->handleCommand(UpdateDto::create($commandData));
        }
    }

    public function editAction()
    {
        $paramProvider = new GenericItem($this->itemParams);
        $editViewTemplate = 'pages/crud-form';
        $successMessage = 'Updated record';
        $contentTitle = null;

        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($this->formClass);
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', $contentTitle);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
        }

        $hasProcessed =
            $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $this->getRequest());

        if (!$hasProcessed && $this->persist && $request->isPost() && $form->isValid()) {
            $commandData = Mapper::mapFromForm($form->getData());

            $response = $this->determineResponse($commandData);

            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = Mapper::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                return $this->redirectTo($response->getResult());
            }

        } elseif (!$request->isPost()) {
            $paramProvider->setParams($this->plugin('params'));
            $itemParams = $paramProvider->provideParameters();
            $response = $this->handleQuery(ItemDto::create($itemParams));

            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $result = $response->getResult();

                $formData = Mapper::mapFromResult($result);

                if (method_exists($this, 'alterFormFor' . $action)) {
                    $form = $this->{'alterFormFor' . $action}($form, $formData);
                }

                $form->setData($formData);
            }
        } elseif (!$form->isValid()) {

            // We need to query the result again to determine the correct actions to remove
            $paramProvider->setParams($this->plugin('params'));
            $itemParams = $paramProvider->provideParameters();
            $response = $this->handleQuery(ItemDto::create($itemParams));

            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $result = $response->getResult();

                $originalData = Mapper::mapFromResult($result);

                $form = $this->setActionButtons($form, $originalData);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->editViewTemplate);
    }

    /**
     * Determines the action DTO to use based on posted form data
     *
     * @param $data
     * @return null
     */
    private function determineAction($postData)
    {
        $allActions = ['grant', 'approve', 'generateDocument', 'cns', 'withdraw', 'refuse', 'reset'];
        foreach ($allActions as $action) {
            if (isset($postData['form-actions'][$action]) && !is_null($postData['form-actions'][$action])) {
                return $action;
            }
        }
        return null;
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Method to alter the form based on status
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForEdit($form, $formData)
    {
        $form = $this->setActionButtons($form, $formData);

        return $form;
    }

    /**
     * Method to alter the form based on status
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForAdd($form, $formData)
    {
        $form = $this->setActionButtons($form, $formData);

        return $form;
    }

    /**
     * Removes action buttons not possible from the form on GET only
     *
     * @param ZendForm $form
     * @param $formData
     * @return ZendForm
     */
    private function setActionButtons(ZendForm $form, $formData)
    {
        $allActions = ['grant', 'approve', 'generateDocument', 'cns', 'withdraw', 'refuse', 'reset'];
        if ($this->params('action') === 'add') {
            foreach ($allActions as $action) {
                $form->get('form-actions')->remove($action);
            }
        } else {
            foreach ($allActions as $action) {
                // we check to see if they are set as the actions come from the backend and
                // are not part of the posted data
                if (!isset($formData['actions']) || !in_array($action, $formData['actions'])) {
                    $form->get('form-actions')->remove($action);
                }
            }
        }

        return $form;
    }
}
