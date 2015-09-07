<?php

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

use Common\Service\Data\CategoryDataService;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmission as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\DeleteSubmission as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmission as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Submission\RefreshSubmissionSections as RefreshDto;
use Dvsa\Olcs\Transfer\Command\Submission\FilterSubmissionSections as FilterDto;

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
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Common\Controller\Traits\GenericUpload;

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SubmissionController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    use GenericUpload;

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

    protected $redirectConfig = [
        'add' => [
            'action' => 'details',
            'resultIdMap' => [
                'submission' => 'submission'
            ]
        ],
        'edit' => [
            'action' => 'details'
        ]
    ];

    /**
     * Stores the submission data
     * @var array
     */
    protected $submissionData;

    /**
     * Temporary storage of the document section sub category id. Used as each section form is generated to extract the
     * relevant documents for that section.
     * @var int
     */
    private $sectionSubcategory;

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
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Submission updated');
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
     * Details action - shows each section detail
     *
     * @return ViewModel
     */
    public function detailsAction()
    {
        $paramProvider = new GenericItem($this->itemParams);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $query = ItemDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->setSubmissionData($data);

                $allSectionsRefData = $this->getAllSectionsRefData();
                $submissionConfig = $this->getSubmissionConfig();

                $this->placeholder()->setPlaceholder(
                    'selectedSectionsArray',
                    $this->generateSelectedSectionsArray($data, $allSectionsRefData, $submissionConfig)
                );

                $this->placeholder()->setPlaceholder('allSections', $allSectionsRefData);
                $this->placeholder()->setPlaceholder('submissionConfig', $submissionConfig['sections']);
                $this->placeholder()->setPlaceholder('submission', $data);
                // to-do $view->setVariable('closeAction', $this->generateCloseActionButtonArray($submission['id']));
                // to-do $view->setVariable('readonly', $submissionService->isClosed($submission['id']));
                $this->placeholder()->setPlaceholder('readonly', (bool) isset($data['closedDate']));

            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }


    /**
     * Updates a section table, to either refresh the data or delete rows
     *
     * @return \Zend\Http\Response
     */
    public function updateTableAction()
    {
        $params['submission'] = $this->params()->fromRoute('submission');
        $formAction = strtolower($this->params()->fromPost('formAction'));

        $this->extractSubmissionData();

        if ($formAction == 'refresh-table') {
            $response = $this->refreshTable();
        } elseif ($formAction == 'delete-row') {
            $response = $this->deleteTableRows();
        } else {
            return $this->notFoundAction();
        }

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Submission updated');
        }

        return $this->redirect()->toRoute(
            'submission',
            ['action' => 'details', 'submission' => $params['submission']],
            [],
            true
        );
    }

    /**
     * Refreshes a single section within the dataSnapshot field of a submission with the latest data
     * from the rest of the database. Redirects back to details page.
     *
     * @return void
     */
    public function refreshTable()
    {
        $paramProvider = new GenericItem($this->itemParams);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $commandData = [
            'id' => $this->params('submission'),
            'version' => $this->params()->fromPost('submissionVersion'),
            'sections' => [$this->params()->fromPost('table')]
        ];

        $response = $this->handleCommand(RefreshDto::create($commandData));

        return $response;
    }

    /**
     * Deletes a single row from a section's list data, reassigns and persists the new data back to dataSnapshot field
     * from the rest of the database. Redirects back to details page.
     *
     * @return \Zend\Http\Response
     */
    public function deleteTableRows()
    {
        $params['case'] = $this->params()->fromRoute('case');
        $params['section'] = $this->params()->fromRoute('section');
        $params['subSection'] = $this->params()->fromRoute('subSection', $params['section']);
        $params['submission'] = $this->params()->fromRoute('submission');

        $rowsToDelete = $this->params()->fromPost('id');
        /** @var \Olcs\Service\Data\Submission $submissionService */
        $submissionService = $this->getServiceLocator()->get('Olcs\Service\Data\Submission');

        $submission = $submissionService->fetchData($params['submission']);
        $snapshotData = json_decode($submission['dataSnapshot'], true);

        if (array_key_exists($params['section'], $snapshotData) &&
            is_array($snapshotData[$params['section']]['data']['tables'][$params['subSection']])) {
            foreach ($snapshotData[$params['section']]['data']['tables'][$params['subSection']] as $key => $dataRow) {
                if (in_array($dataRow['id'], $rowsToDelete)) {
                    unset($snapshotData[$params['section']]['data']['tables'][$params['subSection']][$key]);
                }
            }
            ksort($snapshotData[$params['section']]['data']['tables'][$params['subSection']]);

            $data['id'] = $params['submission'];
            $data['version'] = $submission['version'];
            $data['dataSnapshot'] = json_encode($snapshotData);

            $this->callParentSave($data);
        }
    }

    private function generateSelectedSectionsArray($submission, $allSectionsRefData, $submissionConfig)
    {
        $submissionService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\Submission');

        $selectedSectionsArray =
            $submissionService->extractSelectedSubmissionSectionsData(
                $submission,
                $allSectionsRefData,
                $submissionConfig
            );

        $selectedSectionsArray = $this->generateSectionForms($selectedSectionsArray);

        return $selectedSectionsArray;
    }

    /**
     * Calls Submission Data service. Makes single rest call to ref data table to extract all sections
     * @to-do remove or cache this back end call?
     *
     * @return array
     */
    private function getAllSectionsRefData()
    {
        $submissionService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\Submission');
        return $submissionService->getAllSectionsRefData();
    }

    /**
     * Returns config array for all sections
     * @return mixed
     */
    private function getSubmissionConfig()
    {
        $submissionConfig = $this->getServiceLocator()->get('config')['submission_config'];
        return $submissionConfig;
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

    /**
     * Method to generate and add the section forms for each section to the selectedSectionArray
     *
     * @param array $selectedSectionsArray
     * @return array $selectedSectionsArray
     */
    private function generateSectionForms($selectedSectionsArray)
    {
        $configService = $this->getServiceLocator()->get('config');
        $submissionConfig = $configService['submission_config'];

        if (is_array($selectedSectionsArray)) {
            foreach ($selectedSectionsArray as $sectionId => $sectionData) {

                $this->sectionId = $sectionId;
                // if we allow attachments, then create the attachments form for this section
                if (isset($submissionConfig['sections'][$sectionId]['allow_attachments']) &&
                    $submissionConfig['sections'][$sectionId]['allow_attachments']) {

                    $this->sectionSubcategory = $submissionConfig['sections'][$sectionId]['subcategoryId'];

                    // generate a unique attachment form for this section
                    $attachmentsForm = $this->getSectionForm($this->sectionId);

                    $hasProcessedFiles = $this->processFiles(
                        $attachmentsForm,
                        'attachments',
                        array($this, 'processSectionFileUpload'),
                        array($this, 'deleteSubmissionAttachment'),
                        array($this, 'loadFiles')
                    );

                    $selectedSectionsArray[$sectionId]['attachmentsForm'] = $attachmentsForm;
                }
            }
        }

        return $selectedSectionsArray;
    }

    /**
     * Generates and returns the form object for a given section, changing id and name to ensure no duplicates
     * @param $sectionId
     * @return mixed
     */
    private function getSectionForm($sectionId)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createForm('SubmissionSectionAttachment');

        $form->get('sectionId')->setValue($sectionId);
        $form->setAttribute('id', $sectionId . '-section-attachments');
        $form->setAttribute('name', $sectionId . '-section-attachments');

        return $form;
    }

    /**
     * Callback to handle the file upload
     *
     * @param array $file
     * @return int $id of file
     */
    public function processSectionFileUpload($file)
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            // ensure only the file only uploads to the section we are dealing with
            if ($postData['sectionId'] == $this->sectionId) {
                $data = [
                    'submission' => $this->params()->fromRoute('submission'),
                    'description' => $file['name'],
                    'isExternal' => 0,
                    'category'    => CategoryDataService::CATEGORY_SUBMISSION,
                    'subCategory' => $this->sectionSubcategory,
                ];

                if ($this->uploadFile($file, $data)) {
                    $this->extractSubmissionData();
                }
            }
        }
    }

    /**
     * Handle the file upload
     *
     * @return array
     */
    public function loadFiles()
    {
        $submission = $this->getSubmissionData();
        $sectionDocuments = [];
        foreach ($submission['documents'] as $document) {
            // ensure only the file only uploads to the section we are dealing with by checking subCategory
            if ($document['subCategory']['id'] == $this->sectionSubcategory) {
                $sectionDocuments[] = $document;
            }
        }

        return $sectionDocuments;
    }

    /**
     * Queries backend (not cached) and refresh document list for the submission
     */
    private function extractSubmissionData()
    {
        $paramProvider = new GenericItem($this->itemParams);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $query = ItemDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->setSubmissionData($data);

                return $data;
            }
        }
        return null;
    }

    /**
     * Calls genericUpload::deleteFile() and refreshes the submission data
     *
     * @param $documentId
     * @return bool
     */
    public function deleteSubmissionAttachment($documentId)
    {
        if ($this->deleteFile($documentId)) {
            $this->extractSubmissionData();
        }
        return true;
    }

    /**
     * @param array $submissionData
     */
    public function setSubmissionData($submissionData)
    {
        $this->submissionData = $submissionData;
    }

    /**
     * @return array
     */
    public function getSubmissionData()
    {
        return $this->submissionData;
    }
}
