<?php

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

/*use Common\Service\Data\CategoryDataService;
use Olcs\Controller as OlcsController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits as ControllerTraits;
use ZfcUser\Exception\AuthenticationEventException;
use Common\Controller\Traits\GenericUpload;
*/

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmission as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\DeleteSubmission as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmission as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\Submission as ItemDto;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionList as ListDto;

use Olcs\Form\Model\Form\Submission as SubmissionForm;
use Olcs\Data\Mapper\Submission as SubmissionMapper;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

use Zend\Stdlib\ArrayUtils;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\DeleteItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SubmissionController extends AbstractInternalController  implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    //use ControllerTraits\CaseControllerTrait;
    //use ControllerTraits\CloseActionTrait;
    //use GenericUpload;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_submissions';

    protected $routeIdentifier = 'submission';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'submission';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/wide-layout';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/submission';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'complaint', to => from
    protected $itemParams = ['id' => 'submission'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = SubmissionForm::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = SubmissionMapper::class;

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
        'case' => 'route'
    ];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    protected $deleteModalTitle = 'internal.delete-action-trait.title';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'addAction' => ['forms/submission'],
        'editAction' => ['forms/submission']
    );

    protected $persist = true;

    protected $editViewTemplate = 'pages/crud-form';

    /**
     * Add Action
     * @return mixed|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $defaultDataProvider =  new AddFormDefaultData($this->defaultData);

        $defaultDataProvider->setParams($this->plugin('params'));

        $action = ucfirst($this->params()->fromRoute('action'));

        /** @var \Zend\Form\Form $form */
        $form = $this->getForm($this->formClass);
        $initialData = SubmissionMapper::mapFromResult($defaultDataProvider->provideParameters());

        $form = $this->alterFormForSubmission($form, $initialData);

        $form->setData($initialData);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($this->getRequest()->isPost()) {
            $form->setData((array) $this->params()->fromPost());
        }

        if ($this->persist && $this->getRequest()->isPost() && $form->isValid()) {
            $data = ArrayUtils::merge($initialData, $form->getData());
            $commandData = SubmissionMapper::mapFromForm($data);
            $response = $this->handleCommand(CreateDto::create($commandData));
var_dump($response->getResult());exit;
            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = SubmissionMapper::mapFromErrors($form, $response->getResult());
                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Created record');
                return $this->redirectTo($response->getResult());
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->editViewTemplate);
    }

    /**
     * Edit action
     * @return array|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $paramProvider = new GenericItem($this->itemParams);
        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($this->formClass);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
            $form = $this->alterFormForSubmission($form, $dataFromPost);
        }

        if ($this->persist && $request->isPost() && $form->isValid()) {
            $commandData = SubmissionMapper::mapFromForm($form->getData());
            $response = $this->handleCommand(UpdateDto::create($commandData));

            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = SubmissionMapper::mapFromErrors($form, $response->getResult());

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
                $formData = SubmissionMapper::mapFromResult($result);

                $form = $this->alterFormForSubmission($form, $formData);

                $form->setData($formData);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->editViewTemplate);
    }

    /**
     * Alter Form based on Submission details
     *
     * @param \Common\Controller\Form $form
     * @param array $initialData
     * @return \Common\Controller\Form
     */
    private function alterFormForSubmission($form, $initialData)
    {
        $postData = $this->params()->fromPost('fields');
        //$formData = $this->getDataForForm();

        // Intercept Submission type submit button to prevent saving
        if (isset($postData['submissionSections']['submissionTypeSubmit']) ||
            !(empty($initialData['submissionType']))) {
            $this->persist = false;
        } else {
            // remove form-actions
            $form->remove('form-actions');
        }

        return $form;
    }
}
