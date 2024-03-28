<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Controller\Traits\CheckForCrudAction;
use Common\Form\Form;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTma as CreateForTmaDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTml as CreateForTmlDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\DeleteOtherLicence as DeleteOlDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateForTma as UpdateForTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\CreateForResponsibilities as CreateTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\DeleteForResponsibilities as DeleteTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateForResponsibilities as UpdateTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\DeleteForResponsibilities as DeleteTmlDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities as UpdateTmlDto;
use Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence as OtherLicenceQry;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\GetDocumentsForResponsibilities as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities as GetForResponsibilitiesApp;
use Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetForResponsibilities as GetForResponsibilitiesLic;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\OtherLicence as OtherLicenceMapper;
use Olcs\Data\Mapper\TransportManagerApplication as TransportManagerApplicationMapper;
use Olcs\Data\Mapper\TransportManagerLicence as TransportManagerLicenceMapper;

/**
 * Transport Manager Details Responsibility Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsResponsibilityController extends AbstractTransportManagerDetailsController implements
    LeftViewProvider
{
    use CheckForCrudAction {
        CheckForCrudAction::getActionFromFullActionName as parentGetActionFromFullActionName;
    }

    protected $navigationId = 'transport_manager_details_responsibility';

    protected $responsibilities = null;

    protected $licenceId = null;

    protected $otherLicences = null;

    protected $tmResponsiblitiesDetails = null;

    protected $dtoToType = [
        \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities::class => 'app',
        \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetForResponsibilities::class => 'lic'
    ];

    /**
     * @var \Laminas\Form\FormInterface
     */
    protected $otherLicenceForm;

    protected AnnotationBuilder $transferAnnotationBuilder;
    protected CommandService $commandService;
    protected QueryService $queryService;
    protected NiTextTranslation $niTextTranslationUtil;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        $navigation,
        TransportManagerHelperService $transportManagerHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        QueryService $queryService,
        NiTextTranslation $niTextTranslationUtil,
        FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation,
            $transportManagerHelper,
            $uploadHelper
        );
        $this->transferAnnotationBuilder = $transferAnnotationBuilder;
        $this->commandService = $commandService;
        $this->queryService = $queryService;
        $this->niTextTranslationUtil = $niTextTranslationUtil;
    }

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/details-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return Response|TableBuilder|ViewModel
     */
    public function indexAction()
    {
        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        if ($request->isPost()) {
            $response = $this->checkForCrudAction();

            if ($response instanceof Response) {
                return $response;
            }
        }

        $applicationsTable = $this->getApplicationsTable();
        $licencesTable = $this->getLicencesTable();
        if ($applicationsTable instanceof ViewModel) {
            return $applicationsTable;
        }
        if ($licencesTable instanceof ViewModel) {
            return $licencesTable;
        }

        $tmData  = $this->getTransportManager($this->params('transportManager'));
        // if TM has been removed then make table readonly
        if (!empty($tmData['removedDate'])) {
            $applicationsTable->setDisabled(true);
            $licencesTable->setDisabled(true);
        }

        $this->placeholder()->setPlaceholder('contentTitle', 'Responsibilities');

        $view = $this->getViewWithTm(['tables' => [$applicationsTable, $licencesTable]]);
        $view->setTemplate('pages/multi-tables');

        return $this->renderView($view);
    }

    /**
     * Add TM application action
     *
     * @return Response|\Laminas\Stdlib\ResponseInterface|ViewModel
     */
    public function addAction()
    {
        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        if ($request->isPost() && $this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $form = $this->getForm('TransportManagerApplicationSmall');

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->formPost($form, [$this, 'processAddForm']);

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }
        $this->placeholder()->setPlaceholder('contentTitle', 'Add application');

        return $this->renderView($view);
    }

    /**
     * Get transport manager documents
     *
     * @return array|\Laminas\Http\Response
     */
    public function getDocuments()
    {
        $action = $this->getFromRoute('action');

        $queryToSend = $this->transferAnnotationBuilder
            ->createQuery(
                DocumentsQry::create(
                    [
                        'transportManager' => $this->getFromRoute('transportManager'),
                        'licOrAppId' => $this->getFromRoute('id'),
                        'type' => ($action === 'edit-tm-application') ? 'application' : 'licence'
                    ]
                )
            );

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->queryService->send($queryToSend);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $res = [];
        if ($response->isOk()) {
            $res = $response->getResult()['results'];
        }
        return $res;
    }

    /**
     * Handle the file upload
     *
     * @param array $file File data
     *
     * @return bool
     */
    public function processAdditionalInformationFileUpload($file)
    {
        $action = $this->getFromRoute('action');
        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');

        $dataToSave = $this->transportManagerHelper
            ->getResponsibilityFileData($tmId);
        if ($action === 'edit-tm-application') {
            $key = 'application';
            $data = $this->tmResponsiblitiesDetails['app'];
        } else {
            $data = $this->tmResponsiblitiesDetails['lic'];
            $key = 'licence';
        }
        $dataToSave[$key] = $data[$key]['id'];
        $dataToSave['description'] = $file['name'];

        if ($action === 'edit-tm-application') {
            $dataToSave['licence'] = $data['application']['licence']['id'];
        }
        return $this->uploadFile($file, $dataToSave);
    }

    /**
     * Delete TM application action
     *
     * @return \Laminas\Http\Response
     */
    public function deleteTmApplicationAction()
    {
        return $this->deleteTmRecord(DeleteTmaDto::class);
    }

    /**
     * Delete TM licence action
     *
     * @return \Laminas\Http\Response
     */
    public function deleteTmLicenceAction()
    {
        return $this->deleteTmRecord(DeleteTmlDto::class);
    }

    /**
     * Edit TM application action
     *
     * @return Response|\Laminas\Stdlib\ResponseInterface|ViewModel
     */
    public function editTmApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $titleFlag = $this->getFromRoute('title');
        $tmAppId = $this->getFromRoute('id');
        $title = $titleFlag ? 'Add application' : 'Edit application';

        $tmAppData = $this->getTransportManagerApplication($tmAppId);

        $form = $this->alterEditForm(
            $this->getForm('TransportManagerApplicationOrLicenceFull'),
            $tmAppData['application']
        );

        $processed = $this->processFiles(
            $form,
            'details->file',
            [$this, 'processAdditionalInformationFileUpload'],
            [$this, 'deleteFile'],
            [$this, 'getDocuments']
        );

        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = (array)$request->getPost();
            $form->setData($post);
            if (!$processed) {
                $isCrudAction = isset($post['table']['action']) && $post['table']['action'];
                if ($isCrudAction) {
                    $this->formHelper->disableEmptyValidation($form);
                }
                if ($form->isValid()) {
                    $this->processEditForm($form, !$isCrudAction);
                    if ($isCrudAction) {
                        $this->checkForCrudAction();
                    }
                    if ($this->getResponse()->getContent() !== '') {
                        return $this->getResponse();
                    }
                }
            }
        } else {
            $form->setData($tmAppData);
        }

        $view = $this->getViewWithTm(
            [
                'form' => $form,
                'operatorName' => $tmAppData['application']['licence']['organisation']['name'],
                'applicationId' => $tmAppData['application']['id'],
                'licNo' => $tmAppData['application']['licence']['licNo']
            ]
        );
        $view->setTemplate('sections/transport-manager/pages/tm-responsibility-edit');
        $this->loadScripts(['forms/crud-table-handler']);
        $this->placeholder()->setPlaceholder('contentTitle', $title);

        return $this->renderView($view);
    }

    /**
     * Edit TM licence action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editTmLicenceAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $tmLicId = $this->getFromRoute('id');

        $tmLicData = $this->getTransportManagerLicence($tmLicId);

        $this->licenceId = $tmLicData['licence']['id'];
        $form = $this->alterEditForm($this->getForm('TransportManagerApplicationOrLicenceFull'));

        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        $processed = $this->processFiles(
            $form,
            'details->file',
            [$this, 'processAdditionalInformationFileUpload'],
            [$this, 'deleteFile'],
            [$this, 'getDocuments']
        );

        if ($request->isPost()) {
            $post = (array)$request->getPost();
            $form->setData($post);
            if (!$processed) {
                $isCrudAction = isset($post['table']['action']) && $post['table']['action'];
                if ($isCrudAction) {
                    $this->formHelper->disableEmptyValidation($form);
                }
                if ($form->isValid()) {
                    $this->processEditForm($form, !$isCrudAction);
                    if ($isCrudAction) {
                        $this->checkForCrudAction();
                    }
                    if ($this->getResponse()->getContent() !== '') {
                        return $this->getResponse();
                    }
                }
            }
        } else {
            $form->setData($tmLicData);
        }

        $view = $this->getViewWithTm(
            [
                'form' => $form,
                'operatorName' => $tmLicData['licence']['organisation']['name'] ?? null,
                'licNo' => $tmLicData['licence']['licNo'] ?? null,
            ]
        );

        $view->setTemplate('sections/transport-manager/pages/tm-responsibility-edit');
        $this->loadScripts(['forms/crud-table-handler']);

        return $this->renderView($view, 'Edit licence');
    }

    /**
     * Get transport manager application
     *
     * @param int $tmAppId TM application id
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function getTransportManagerApplication($tmAppId)
    {
        return $this->getTransportManagerApplicationOrLicence(
            GetForResponsibilitiesApp::class,
            TransportManagerApplicationMapper::class,
            $tmAppId
        );
    }

    /**
     * Get transport manager licence
     *
     * @param int $tmLicenceId TM licence id
     *
     * @return Response|ViewModel
     */
    protected function getTransportManagerLicence($tmLicenceId)
    {
        return $this->getTransportManagerApplicationOrLicence(
            GetForResponsibilitiesLic::class,
            TransportManagerLicenceMapper::class,
            $tmLicenceId
        );
    }

    /**
     * Get transport manager application or licence
     *
     * @param string $dtoClass    Dto class
     * @param string $mapperClass Mapper class
     * @param int    $id          Id
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function getTransportManagerApplicationOrLicence($dtoClass, $mapperClass, $id)
    {
        $queryToSend = $this->transferAnnotationBuilder
            ->createQuery(
                $dtoClass::create(['id' => $id])
            );

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->queryService->send($queryToSend);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }
        $res = [];
        if ($response->isOk()) {
            $result = $response->getResult();
            $this->tmResponsiblitiesDetails[$this->dtoToType[$dtoClass]] = $result;
            $res = $mapperClass::mapFromResult($result);
            $this->otherLicences = $res['otherLicences'];
        }
        return $res;
    }

    /**
     * Check for alternative crud action
     * Need this to handle edit other licence action when clicking the table link
     *
     * @param string|array $action Action
     *
     * @return \Laminas\Http\Response|void
     */
    protected function checkForAlternativeCrudAction($action)
    {
        $params = [];
        if (is_array($action) && count($action) == 1) {
            $key = key($action);
            if (is_array($action[$key]) && count($action[$key]) === 1) {
                $id = key($action[$key]);
                $params = [
                    'id' => $id,
                    'action' => $key,
                ];
            }
            return $this->redirect()->toRoute(null, $params, [], true);
        }
    }

    /**
     * Delete TM application or licence
     *
     * @param string    $dtoClass         Dto class
     * @param int|array $idToDelete       Id(s) to delete
     * @param string    $redirectToAction Redirect to action
     * @param int       $redirectToId     Redirect to id
     *
     * @return Response|ViewModel
     */
    protected function deleteTmRecord($dtoClass, $idToDelete = null, $redirectToAction = '', $redirectToId = null)
    {
        if ($this->isButtonPressed('cancel')) {
            if ($redirectToAction) {
                return $this->redirectToAction($redirectToAction, $redirectToId);
            } else {
                return $this->redirectToIndex();
            }
        }
        $id = $idToDelete ?: $this->getFromRoute('id');
        if (!$id) {
            $ids = $this->params()->fromQuery('id');
        } elseif (!is_array($id)) {
            $ids = [$id];
        } else {
            $ids = $id;
        }
        $translator = $this->translationHelper;
        $response = $this->confirm(
            $translator->translate('transport-manager.responsibilities.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dto = $dtoClass::create(['ids' => $ids]);
        $command = $this->transferAnnotationBuilder->createCommand($dto);
        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->commandService->send($command);
        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }
        $this->addSuccessMessage('Deleted successfully');

        if ($redirectToAction) {
            return $this->redirectToAction($redirectToAction, $redirectToId);
        }
        return $this->redirectToIndex();
    }

    /**
     * Get applications table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getApplicationsTable()
    {
        $tableData = $this->getResponsibilitiesData('tmApplications');
        if (!is_array($tableData)) {
            return $tableData;
        }

        return $this->getTable('tm.applications', $tableData);
    }

    /**
     * Get licences table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getLicencesTable()
    {
        $tableData = $this->getResponsibilitiesData('tmLicences');
        if (!is_array($tableData)) {
            return $tableData;
        }

        return $this->getTable('tm.licences', $tableData);
    }

    /**
     * Get responsibilities data
     *
     * @param string $type Type
     *
     * @return array|\Laminas\Http\Response
     */
    protected function getResponsibilitiesData($type)
    {
        if ($this->responsibilities === null) {
            $query = [
                'transportManager' => $this->params('transportManager')
            ];
            $queryToSend = $this->transferAnnotationBuilder
                ->createQuery(
                    TmResponsibilitiesList::create($query)
                );

            /**
 * @var \Common\Service\Cqrs\Response $response
*/
            $response = $this->queryService->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $result = $response->getResult();
                $this->responsibilities['tmLicences'] =
                    TransportManagerLicenceMapper::mapFromResultForTable($result);
                $this->responsibilities['tmApplications'] =
                    TransportManagerApplicationMapper::mapFromResultForTable($result);
            }
        }
        return $this->responsibilities[$type];
    }

    /**
     * Process form and redirect back to list or to the next step
     *
     * @param array $data Data
     *
     * @return null|\Laminas\Http\Response
     */
    protected function processAddForm(array $data)
    {
        $data = $data['validData'];
        $tm = $this->getFromRoute('transportManager');

        $routeParams = ['transportManager' => $tm, 'action' => 'edit-tm-application', 'title' => 1];

        $dto = CreateTmaDto::create(
            [
                'application' => $data['details']['application'],
                'transportManager' => $tm,
            ]
        );
        $command = $this->transferAnnotationBuilder->createCommand($dto);

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->commandService->send($command);
        if ($response->isClientError()) {
            foreach ($response->getResult()['messages'] as $message) {
                $this->flashMessengerHelper->addErrorMessage($message);
            }
        }

        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            $routeParams['id'] = $result['id']['transportManagerApplication'];
            return $this->redirectToRoute(
                'transport-manager/details/responsibilities',
                $routeParams
            );
        }

        return null;
    }

    /**
     * Alter edit form
     *
     * @param \Common\Form\Form $form        Form
     * @param array             $application Array of application data
     *
     * @return \Common\Form\Form
     */
    protected function alterEditForm(\Common\Form\Form $form, $application = [])
    {
        // Add in the NI translations. Eg for form element labels
        $niTranslation = $this->niTextTranslationUtil;
        if (!empty($application['niFlag'])) {
            $niTranslation->setLocaleForNiFlag($application['niFlag']);
        }

        $this->transportManagerHelper->removeTmTypeBothOption($form->get('details')->get('tmType'));
        $this->transportManagerHelper->populateOtherLicencesTable(
            $form->get('details')->get('otherLicences'),
            $this->getOtherLicencesTable()
        );

        $tmStatus = $form->get('details')->get('tmApplicationStatus');
        $this->formHelper->removeValueOption($tmStatus, 'tmap_st_details_submitted');
        $this->formHelper->removeValueOption($tmStatus, 'tmap_st_details_checked');
        $this->formHelper->removeValueOption($tmStatus, 'tmap_st_operator_approved');

        return $form;
    }

    /**
     * Get other licences table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getOtherLicencesTable()
    {
        $action = $this->getFromRoute('action');
        if ($action === 'edit-tm-application') {
            $tableName = 'tm.otherlicences-applications';
        } else {
            $tableName = 'tm.otherlicences-licences';
        }
        return $this->tableFactory->prepareTable($tableName, $this->otherLicences);
    }

    /**
     * Process form and redirect back to list
     *
     * @param \Laminas\Form\FormInterface $form        Form
     * @param bool                        $showMessage Show message
     *
     * @return null|\Laminas\Http\Response
     */
    protected function processEditForm($form, $showMessage = true)
    {
        $data = $form->getData();
        $action = $this->getFromRoute('action');

        if ($action === 'edit-tm-application') {
            $message = 'The application has been updated';
            $mappedData = TransportManagerApplicationMapper::mapFromForm($data);
            $dto = UpdateTmaDto::create($mappedData);
        } else {
            $message = 'The licence has been updated';
            $mappedData = TransportManagerLicenceMapper::mapFromForm($data);
            $dto = UpdateTmlDto::create($mappedData);
        }

        $command = $this->transferAnnotationBuilder->createCommand($dto);

        /**
        * @var \Common\Service\Cqrs\Response $response
        */
        $response = $this->commandService->send($command);
        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];
            if ($action === 'edit-tm-application') {
                $errors = TransportManagerApplicationMapper::mapFromErrors($form, $messages);
            } else {
                $errors = TransportManagerLicenceMapper::mapFromErrors($form, $messages);
            }
            if ($errors) {
                foreach ($errors as $error) {
                    $this->flashMessengerHelper->addErrorMessage($error);
                }
            }
        }

        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            if ($showMessage) {
                $this->flashMessenger()->addSuccessMessage($message);
            }
            return $this->redirectToIndex();
        }

        return null;
    }

    /**
     * Add other licence action, calling from licence edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addOtherLicenceLicencesAction()
    {
        return $this->formAction('Add', 'edit-tm-licence');
    }

    /**
     * Add other licence action, calling from application edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addOtherLicenceApplicationsAction()
    {
        return $this->formAction('Add', 'edit-tm-application');
    }

    /**
     * Edit other licence action, calling from licence edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editOtherLicenceLicencesAction()
    {
        return $this->formAction('Edit', 'edit-tm-licence');
    }

    /**
     * Edit other licence action, calling from application edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editOtherLicenceApplicationsAction()
    {
        return $this->formAction('Edit', 'edit-tm-application');
    }

    /**
     * Delete other licence action, calling from application edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteOtherLicenceApplicationsAction()
    {
        return $this->deleteOtherLicence('edit-tm-application');
    }

    /**
     * Delete other licence action, calling from licence edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteOtherLicenceLicencesAction()
    {
        return $this->deleteOtherLicence('edit-tm-licence');
    }

    /**
     * Delete other licence action
     *
     * @param string $redirectAction Redirect action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteOtherLicence($redirectAction)
    {
        $ids = $this->params()->fromQuery('id');
        if (!is_array($ids)) {
            $ids = $this->params()->fromRoute('id');
        }
        $otherLicenceId = is_array($ids) ? $ids[0] : $ids;
        $recordId = $this->getTmRecordId($otherLicenceId);
        return $this->deleteTmRecord(DeleteOlDto::class, $ids, $redirectAction, $recordId);
    }

    /**
     * Get required tm record id by othere licence id
     *
     * @param int $otherLicenceId Other licence id
     *
     * @return array|\Laminas\Http\Response
     */
    protected function getTmRecordId($otherLicenceId)
    {
        $queryToSend = $this->transferAnnotationBuilder
            ->createQuery(OtherLicenceQry::create(['id' => $otherLicenceId]));

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->queryService->send($queryToSend);
        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $res = $response->getResult();
        if (isset($res['transportManagerLicence']['id'])) {
            $key = 'transportManagerLicence';
        } else {
            $key = 'transportManagerApplication';
        }
        return $res[$key]['id'];
    }

    /**
     * Form action
     *
     * @param string $type           Type
     * @param string $redirectAction Redirect action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function formAction($type, $redirectAction)
    {
        if ($type === 'Add') {
            $redirectId = $this->fromRoute('id');
        } else {
            $redirectId = $this->getTmRecordId($this->fromRoute('id'));
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToAction($redirectAction, $redirectId);
        }

        $form = $this->getForm('TmOtherLicence');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId);
        }

        $this->otherLicenceForm = $form;
        $this->formPost($form, [$this, 'processOtherLicenceForm']);

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        return $this->renderView(
            $view,
            'internal.transport_manager.responsibilities.other_licence_' . strtolower($type)
        );
    }

    /**
     * Populate other licence edit form
     *
     * @param \Laminas\Form\FormInterface $form           Form
     * @param string                      $type           Type
     * @param string                      $redirectAction Redirect action
     * @param int                         $redirectId     Redirect id
     *
     * @return \Laminas\Form\Form
     */
    protected function populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId)
    {
        if ($type === 'Edit') {
            $queryToSend = $this->transferAnnotationBuilder
                ->createQuery(OtherLicenceQry::create(['id' => $this->fromRoute('id')]));

            /**
 * @var \Common\Service\Cqrs\Response $response
*/
            $response = $this->queryService->send($queryToSend);
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
            $data = [];
            if ($response->isOk()) {
                $data = OtherLicenceMapper::mapFromResult($response->getResult());
            }
        }
        $data['data']['redirectAction'] = $redirectAction;
        $data['data']['redirectId'] = $redirectId;
        $form->setData($data);
        return $form;
    }

    /**
     * Process form and redirect back to list
     *
     * @param array $data Data
     *
     * @return \Laminas\Http\Response|void
     */
    protected function processOtherLicenceForm($data)
    {
        $data = $data['validData'];
        $mappedData = OtherLicenceMapper::mapFromForm($data);
        if (isset($data['data']['id']) && $data['data']['id']) {
            $dtoClass = UpdateForTmaDto::class;
        } elseif ($data['data']['redirectAction'] === 'edit-tm-application') {
            $dtoClass = CreateForTmaDto::class;
        } else {
            $dtoClass = CreateForTmlDto::class;
        }
        $dto = $dtoClass::create($mappedData);
        $command = $this->transferAnnotationBuilder->createCommand($dto);
        /**
         ** @var \Common\Service\Cqrs\Response $response
         */
        $response = $this->commandService->send($command);
        if ($response->isClientError()) {
            $errors = OtherLicenceMapper::mapFromErrors($this->otherLicenceForm, $response->getResult()['messages']);
            if ($errors) {
                foreach ($errors as $error) {
                    $this->flashMessengerHelper->addErrorMessage($error);
                }
            }
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
        if ($response->isOk()) {
            return $this->redirectToAction($data['data']['redirectAction'], $data['data']['redirectId']);
        }
    }

    /**
     * Redirect to given action
     *
     * @param string $action Action
     * @param int    $id     Id
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToAction($action, $id)
    {
        $tm = $this->getFromRoute('transportManager');
        $routeParams = [
            'transportManager' => $tm,
            'action' => $action,
            'id' => $id
        ];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }

    /**
     * Override parent method
     *
     * @param string $action Action
     *
     * @return string
     */
    protected function getActionFromFullActionName($action = null)
    {
        if ($action === 'add-other-licence-applications' || $action === 'add-other-licence-licences') {
            return 'add';
        }

        return $this->parentGetActionFromFullActionName($action);
    }
}
