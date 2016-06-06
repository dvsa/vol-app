<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Controller\Traits\CheckForCrudAction;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTma as CreateForTmaDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTml as CreateForTmlDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\DeleteOtherLicence as DeleteOlDto;
use Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateForTma as UpdateForTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\CreateForResponsibilities as CreateTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\DeleteForResponsibilities as DeleteTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateForResponsibilities as UpdateTmaDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\DeleteForResponsibilities as DeleteTmlDto;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities as UpdateTmlDto;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres as OperatingCentresQry;
use Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence as OtherLicenceQry;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\GetDocumentsForResponsibilities as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities as GetForResponsibilitiesApp;
use Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetForResponsibilities as GetForResponsibilitiesLic;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\OperatingCentres as OperatingCentresMapper;
use Olcs\Data\Mapper\OtherLicence as OtherLicenceMapper;
use Olcs\Data\Mapper\TransportManagerApplication as TransportManagerApplicationMapper;
use Olcs\Data\Mapper\TransportManagerLicence as TransportManagerLicenceMapper;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

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

    protected $operatingCentres = null;

    protected $otherLicences = null;

    protected $tmResponsiblitiesDetails = null;

    protected $dtoToType = [
        'Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities' => 'app',
        'Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetForResponsibilities' => 'lic'
    ];

    /** @var  \Zend\Form\FormInterface */
    protected $otherLicenceForm;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/details-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
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
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost() && $this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $form = $this->getForm('TransportManagerApplicationSmall');

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->formPost($form, 'processAddForm');

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }
        $this->placeholder()->setPlaceholder('contentTitle', 'Add application');

        return $this->renderView($view);
    }

    /**
     * Get transport manager documents
     *
     * @return array
     */
    public function getDocuments()
    {
        $action = $this->getFromRoute('action');

        $queryToSend = $this->getServiceLocator()
            ->get('TransferAnnotationBuilder')
            ->createQuery(
                DocumentsQry::create(
                    [
                        'transportManager' => $this->getFromRoute('transportManager'),
                        'licOrAppId' => $this->getFromRoute('id'),
                        'type' => ($action === 'edit-tm-application') ? 'application' : 'licence'
                    ]
                )
            );

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @param array $file
     * @return array
     */
    public function processAdditionalInformationFileUpload($file)
    {
        $action = $this->getFromRoute('action');
        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');

        $dataToSave = $this->getServiceLocator()->get('Helper\TransportManager')
            ->getResponsibilityFileData($tmId, $file);
        if ($action === 'edit-tm-application') {
            $key = 'application';
            $data = $this->tmResponsiblitiesDetails['app'];
        } else {
            $data = $this->tmResponsiblitiesDetails['lic'];
            $key = 'licence';
        }
        $dataToSave[$key] = $data[$key]['id'];

        if ($action === 'edit-tm-application') {
            $dataToSave['licence'] = $data['application']['licence']['id'];
        }
        return $this->uploadFile($file, $dataToSave);
    }

    /**
     * Delete TM application action
     */
    public function deleteTmApplicationAction()
    {
        return $this->deleteTmRecord(DeleteTmaDto::class);
    }

    /**
     * Delete TM licence action
     */
    public function deleteTmLicenceAction()
    {
        return $this->deleteTmRecord(DeleteTmlDto::class);
    }

    /**
     * Edit TM application action
     *
     * @return \Zend\View\Model\ViewModel
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
            $tmAppData['application']['id']
        );

        $processed = $this->processFiles(
            $form,
            'details->file',
            array($this, 'processAdditionalInformationFileUpload'),
            array($this, 'deleteFile'),
            array($this, 'getDocuments')
        );

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = (array)$request->getPost();
            $form->setData($post);
            if (!$processed) {
                $isCrudAction = isset($post['table']['action']) && $post['table']['action'];
                if ($isCrudAction) {
                    $this->getServiceLocator()->get('Helper\Form')->disableEmptyValidation($form);
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
     * @return \Zend\View\Model\ViewModel
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

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $processed = $this->processFiles(
            $form,
            'details->file',
            array($this, 'processAdditionalInformationFileUpload'),
            array($this, 'deleteFile'),
            array($this, 'getDocuments')
        );

        if ($request->isPost()) {
            $post = (array)$request->getPost();
            $form->setData($post);
            if (!$processed) {
                $isCrudAction = isset($post['table']['action']) && $post['table']['action'];
                if ($isCrudAction) {
                    $this->getServiceLocator()->get('Helper\Form')->disableEmptyValidation($form);
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
                'operatorName' => $tmLicData['licence']['organisation']['name'],
                'licNo' => $tmLicData['licence']['licNo']
            ]
        );

        $view->setTemplate('sections/transport-manager/pages/tm-responsibility-edit');
        $this->loadScripts(['forms/crud-table-handler']);

        return $this->renderView($view, 'Edit licence');
    }

    protected function getTransportManagerApplication($tmAppId)
    {
        return $this->getTransportManagerApplicationOrLicence(
            GetForResponsibilitiesApp::class,
            TransportManagerApplicationMapper::class,
            $tmAppId
        );
    }

    protected function getTransportManagerLicence($tmLicenceId)
    {
        return $this->getTransportManagerApplicationOrLicence(
            GetForResponsibilitiesLic::class,
            TransportManagerLicenceMapper::class,
            $tmLicenceId
        );
    }

    protected function getTransportManagerApplicationOrLicence($dtoClass, $mapperClass, $id)
    {
        $queryToSend = $this->getServiceLocator()
            ->get('TransferAnnotationBuilder')
            ->createQuery(
                $dtoClass::create(['id' => $id])
            );

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @param $action mixed
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
     * @param string $dtoClass
     * @param int|array $idToDelete
     * @param string $redirectToAction
     * @param int $redirectToId
     * @return Redirect
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
        $id = ($idToDelete) ? $idToDelete : $this->getFromRoute('id');
        if (!$id) {
            $ids = $this->params()->fromQuery('id');
        } elseif (!is_array($id)) {
            $ids = [$id];
        } else {
            $ids = $id;
        }
        $translator = $this->getServiceLocator()->get('translator');
        $response = $this->confirm(
            $translator->translate('transport-manager.responsibilities.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dto = $dtoClass::create(['ids' => $ids]);
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }
        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @return TableBuilder
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
     * @return TableBuilder
     */
    protected function getLicencesTable()
    {
        $tableData = $this->getResponsibilitiesData('tmLicences');
        if (!is_array($tableData)) {
            return $tableData;
        }

        return $this->getTable('tm.licences', $tableData);
    }

    protected function getResponsibilitiesData($type)
    {
        if ($this->responsibilities === null) {
            $query = [
                'transportManager' => $this->params('transportManager')
            ];
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    TmResponsibilitiesList::create($query)
                );

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);
            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @param array $data
     */
    protected function processAddForm($data)
    {
        $tm = $this->getFromRoute('transportManager');

        $routeParams = ['transportManager' => $tm, 'action' => 'edit-tm-application', 'title' => 1];

        $dto = CreateTmaDto::create(
            [
                'application' => $data['details']['application'],
                'transportManager' => $tm,
            ]
        );
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);
        if ($response->isClientError()) {
            foreach ($response->getResult()['messages'] as $message) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
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
     * @param \Zend\Form\FormInterface $form
     * @return \Zend\Form\Form
     */
    protected function alterEditForm(\Zend\Form\FormInterface $form, $appId = null)
    {
        $action = $this->getFromRoute('action');

        if ($action === 'edit-tm-licence') {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->tmApplicationStatus');
            $params = [
                'type'       => 'licence',
                'identifier' => $this->licenceId
            ];
        } else {
            $params = [
                'type'       => 'application',
                'identifier' => $appId
            ];
        }

        $ocOptions = $this->getOcForListBox($params);

        // @NOTE This logic has been moved to the helper service, so it can be re-used
        $this->getServiceLocator()->get('Helper\TransportManager')
            ->alterResponsibilitiesFieldset($form->get('details'), $ocOptions, $this->getOtherLicencesTable());

        return $form;
    }

    protected function getOcForListBox($params)
    {
        if ($this->operatingCentres === null) {
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    OperatingCentresQry::create($params)
                );

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            $ocOptions = [];
            if ($response->isOk()) {
                $ocOptions = OperatingCentresMapper::mapFromResult(
                    $response->getResult()
                );
            }

            $this->operatingCentres = $ocOptions;
        }

        return $this->operatingCentres;
    }

    /**
     * Get other licences table
     */
    protected function getOtherLicencesTable()
    {
        $action = $this->getFromRoute('action');
        if ($action === 'edit-tm-application') {
            $tableName = 'tm.otherlicences-applications';
        } else {
            $tableName = 'tm.otherlicences-licences';
        }
        return $this->getServiceLocator()->get('Table')->prepareTable($tableName, $this->otherLicences);
    }

    /**
     * Process form and redirect back to list
     *
     * @param \Zend\Form\FormInterface $form
     * @param bool $showMessage
     * @return \Zend\Http\Response
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

        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);
        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];
            if ($action === 'edit-tm-application') {
                $errors = TransportManagerApplicationMapper::mapFromErrors($form, $messages);
            } else {
                $errors = TransportManagerLicenceMapper::mapFromErrors($form, $messages);
            }
            if ($errors) {
                foreach ($errors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }
        }

        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            if ($showMessage) {
                // @todo: There is a bug. Messages can't be displayed after the redirect. Need to fix in future stories.
                $this->flashMessenger()->addSuccessMessage($message);
            }
            return $this->redirectToIndex();
        }

        return null;
    }

    /**
     * Add other licence action, calling from licence edit action
     *
     */
    public function addOtherLicenceLicencesAction()
    {
        return $this->formAction('Add', 'edit-tm-licence');
    }

    /**
     * Add other licence action, calling from application edit action
     *
     */
    public function addOtherLicenceApplicationsAction()
    {
        return $this->formAction('Add', 'edit-tm-application');
    }

    /**
     * Edit other licence action, calling from licence edit action
     *
     */
    public function editOtherLicenceLicencesAction()
    {
        return $this->formAction('Edit', 'edit-tm-licence');
    }

    /**
     * Edit other licence action, calling from application edit action
     *
     */
    public function editOtherLicenceApplicationsAction()
    {
        return $this->formAction('Edit', 'edit-tm-application');
    }

    /**
     * Delete other licence action, calling from application edit action
     *
     */
    public function deleteOtherLicenceApplicationsAction()
    {
        return $this->deleteOtherLicence('edit-tm-application');
    }

    /**
     * Delete other licence action, calling from licence edit action
     *
     */
    public function deleteOtherLicenceLicencesAction()
    {
        return $this->deleteOtherLicence('edit-tm-licence');
    }

    /**
     * Delete other licence action
     *
     * @param string $redirectAction
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
     * @param int $otherLicenceId
     * @return array
     */
    protected function getTmRecordId($otherLicenceId)
    {
        $queryToSend = $this->getServiceLocator()
            ->get('TransferAnnotationBuilder')
            ->createQuery(OtherLicenceQry::create(['id' => $otherLicenceId]));

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }
        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @param string $type
     * @param string $redirectAction
     * @return mixed
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

        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getForm('TmOtherLicence');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId);
        }

        $this->otherLicenceform = $form;
        $this->formPost($form, 'processOtherLicenceForm');

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
     * @form Zend\Form\Form
     * @param \Zend\Form\FormInterface $form
     * @param string $type
     * @param string $redirectAction
     * @param int $redirectId
     * @return \Zend\Form\Form
     */
    protected function populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId)
    {
        if ($type === 'Edit') {
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(OtherLicenceQry::create(['id' => $this->fromRoute('id')]));

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);
            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }
            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
     * @param array $data
     * @return \Zend\Http\Response
     */
    protected function processOtherLicenceForm($data)
    {
        $mappedData = OtherLicenceMapper::mapFromForm($data);
        if (isset($data['data']['id']) && $data['data']['id']) {
            $dtoClass = UpdateForTmaDto::class;
        } elseif ($data['data']['redirectAction'] === 'edit-tm-application') {
            $dtoClass = CreateForTmaDto::class;
        } else {
            $dtoClass = CreateForTmlDto::class;
        }
        $dto = $dtoClass::create($mappedData);
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);
        if ($response->isClientError()) {
            $errors = OtherLicenceMapper::mapFromErrors($this->otherLicenceForm, $response->getResult()['messages']);
            if ($errors) {
                foreach ($errors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
        if ($response->isOk()) {
            $this->redirectToAction($data['data']['redirectAction'], $data['data']['redirectId']);
        }
    }

    /**
     * Redirect to given action
     *
     * @param string $action
     * @param int $id
     * @return \Zend\Http\Response
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
     * @param string $action
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
