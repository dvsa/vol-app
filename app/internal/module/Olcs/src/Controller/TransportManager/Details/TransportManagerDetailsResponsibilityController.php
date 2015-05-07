<?php

/**
 * Transport Manager Details Responsibility Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Zend\Http\Response;
use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use Common\Service\Data\LicenceOperatingCentre;
use Common\Service\Entity\TransportManagerApplicationEntityService;

/**
 * Transport Manager Details Responsibility Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsResponsibilityController extends AbstractTransportManagerDetailsController
{
    /**
     * @var string
     */
    protected $section = 'details-responsibilities';

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $response = $this->checkForCrudAction();

            if ($response instanceof Response) {
                return $response;
            }
        }

        $applicationsTable = $this->getApplicationsTable();
        $licencesTable = $this->getLicencesTable();

        $view = $this->getViewWithTm(['tables' => [$applicationsTable, $licencesTable]]);
        $view->setTemplate('pages/multi-tables');

        return $this->renderView($view);
    }

    /**
     * Add TM application action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost() && $this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $form = $this->getForm('transport-manager-application-small');

        if ($request->isPost()) {

            $post = (array)$request->getPost();

            $applicationId = $post['details']['application'];

            $appIdValidator = $this->getServiceLocator()->get('applicationIdValidator');

            $appData = $this->getServiceLocator()->get('Entity\Application')->getLicenceType($applicationId);

            $appIdValidator->setAppData($appData);

            $appIdValidator->setTmAppData(
                $this->getServiceLocator()
                    ->get('Entity\TransportManagerApplication')
                    ->getByApplicationTransportManager(
                        $applicationId,
                        $this->getFromRoute('transportManager')
                    )['Results']
            );

            $applicationValidatorChain =
                $form->getInputFilter()->get('details')->get('application')->getValidatorChain();

            $applicationValidatorChain->attach($appIdValidator);
        }

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('partials/form');

        $this->formPost($form, 'processAddForm');

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        return $this->renderView($view, 'Add application');
    }

    /**
     * Get transport manager documents
     *
     * @return array
     */
    public function getDocuments()
    {
        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');

        if ($this->getFromRoute('action') === 'edit-tm-application') {
            return $this->getServiceLocator()->get('Helper\TransportManager')->getResponsibilityFiles($tmId, $id);
        }

        $data = $this->getServiceLocator()->get('Entity\TransportManagerLicence')->getTransportManagerLicence($id);

        return $this->getServiceLocator()->get('Entity\TransportManager')
            ->getDocuments(
                $tmId,
                $data['licence']['id'],
                'licence',
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
            );
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

        if ($action == 'edit-tm-application') {
            $service = 'Entity\TransportManagerApplication';
            $method = 'getTransportManagerApplication';
            $key = 'application';
        } else {
            $service = 'Entity\TransportManagerLicence';
            $method = 'getTransportManagerLicence';
            $key = 'licence';
        }
        $data = $this->getServiceLocator()->get($service)->$method($id);
        $dataToSave[$key] = $data[$key]['id'];

        if ($action == 'edit-tm-application') {
            $dataToSave['licence'] = $data['application']['licence']['id'];
        }
        return $this->uploadFile($file, $dataToSave);
    }

    /**
     * Delete TM application action
     */
    public function deleteTmApplicationAction()
    {
        return $this->deleteTmRecord('Entity\TransportManagerApplication');
    }

    /**
     * Delete TM licence action
     */
    public function deleteTmLicenceAction()
    {
        return $this->deleteTmRecord('Entity\TransportManagerLicence');
    }

    /**
     * Edit TM application action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editTmApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $titleFlag = $this->getFromRoute('title', 0);
        $tmAppId = $this->getFromRoute('id');
        $title = $titleFlag ? 'Add application' : 'Edit application';

        $tmAppData = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerApplication($tmAppId);

        $form = $this->alterEditForm(
            $this->getForm('TransportManagerApplicationOrLicenceFull'),
            $tmAppData['application']['id']
        );

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
                    $this->processEditForm($form->getData(), !$isCrudAction);
                    if ($isCrudAction) {
                        $this->checkForCrudAction();
                    }
                    if ($this->getResponse()->getContent() !== '') {
                        return $this->getResponse();
                    }
                }
            }
        } else {
            $form = $this->populateEditForm($form, $tmAppData);
        }

        $view = $this->getViewWithTm(
            [
                'form' => $form,
                'operatorName' => $tmAppData['application']['licence']['organisation']['name'],
                'applicationId' => $tmAppData['application']['id'],
                'licNo' => $tmAppData['application']['licence']['licNo']
            ]
        );
        $view->setTemplate('pages/transport-manager/tm-responsibility-edit');
        $this->loadScripts(['forms/crud-table-handler']);

        return $this->renderView($view, $title);
    }

    /**
     * Edit TM licence action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editTmLicenceAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $tmLicId = $this->getFromRoute('id');

        $tmLicData = $this->getServiceLocator()->get('Entity\TransportManagerLicence')
            ->getTransportManagerLicence($tmLicId);

        $licenceOcService = $this->getServiceLocator()->get('Common\Service\Data\LicenceOperatingCentre');

        $licenceService = $this->getServiceLocator()->get('Common\Service\Data\Licence');
        $licenceService->setId($tmLicData['licence']['id']);
        $licenceOcService->setOutputType(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL);

        $form = $this->alterEditForm($this->getForm('TransportManagerApplicationOrLicenceFull'));

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
                    $this->processEditForm($form->getData(), !$isCrudAction);
                    if ($isCrudAction) {
                        $this->checkForCrudAction();
                    }
                    if ($this->getResponse()->getContent() !== '') {
                        return $this->getResponse();
                    }
                }
            }
        } else {
            $form = $this->populateEditForm($form, $tmLicData);
        }

        $view = $this->getViewWithTm(
            [
                'form' => $form,
                'operatorName' => $tmLicData['licence']['organisation']['name'],
                'licNo' => $tmLicData['licence']['licNo']
            ]
        );

        $view->setTemplate('pages/transport-manager/tm-responsibility-edit');
        $this->loadScripts(['forms/crud-table-handler']);

        return $this->renderView($view, 'Edit licence');
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
     * @param string $serviceName
     * @param int|array $idToDelete
     * @param string $redirectToAction
     * @param int $redirectToId
     * @return Redirect
     */
    protected function deleteTmRecord($serviceName, $idToDelete = null, $redirectToAction = '', $redirectToId = null)
    {
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

        if (!$this->isButtonPressed('cancel')) {
            $this->getServiceLocator()->get($serviceName)->deleteListByIds(['id' => $ids]);
            $this->addSuccessMessage('Deleted successfully');
        }

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
        $transportManagerId = $this->params('transportManager');

        $status = [
            'apsts_consideration',
            'apsts_not_submitted',
            'apsts_granted'
        ];

        $results = $this->getServiceLocator()
            ->get('Entity\TransportManagerApplication')
            ->getTransportManagerApplications($transportManagerId, $status);

        return $this->getTable('tm.applications', $results);
    }

    /**
     * Get licences table
     *
     * @return TableBuilder
     */
    protected function getLicencesTable()
    {
        $transportManagerId = $this->params('transportManager');

        $status = [
            'lsts_valid',
            'lsts_suspended',
            'lsts_curtailed'
        ];

        $results = $this->getServiceLocator()
            ->get('Entity\TransportManagerLicence')
            ->getTransportManagerLicences($transportManagerId, $status);

        return $this->getTable('tm.licences', $results);
    }

    /**
     * Process form and redirect back to list or to the next step
     *
     * @param array $data
     * @return redirect
     */
    protected function processAddForm($data)
    {
        $tm = $this->getFromRoute('transportManager');

        $routeParams = ['transportManager' => $tm, 'action' => 'edit-tm-application', 'title' => 1];

        $application = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getHeaderData($data['details']['application']);

        $action = $this->getServiceLocator()
            ->get('Processing\GrantTransportManager')
            ->licenceHasTransportManager($tm, $application['licence']['id']) ? 'U' : 'A';

        $transportManagerApplication = [
            'application' => $data['details']['application'],
            'transportManager' => $tm,
            'action' => $action,
            'tmApplicationStatus' => TransportManagerApplicationEntityService::STATUS_POSTAL_APPLICATION,
        ];

        $result = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->save($transportManagerApplication);

        $routeParams['id'] = $result['id'];

        return $this->redirectToRoute('transport-manager/details/responsibilities', $routeParams);
    }

    /**
     * Alter edit form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterEditForm($form, $appId = null)
    {
        $action = $this->getFromRoute('action');
        if ($action == 'edit-tm-licence') {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->tmApplicationStatus');
            $ocOptions = $this->getServiceLocator()->get('Common\Service\Data\LicenceOperatingCentre')
                ->fetchListOptions([]);
        } else {
            $ocOptions = $this->getServiceLocator()->get('Entity\ApplicationOperatingCentre')
                ->getForSelect($appId);
        }

        // @NOTE This logic has been moved to the helper service, so it can be re-used
        $this->getServiceLocator()->get('Helper\TransportManager')
            ->alterResponsibilitiesFieldset($form->get('details'), $ocOptions, $this->getOtherLicencesTable());

        return $form;
    }

    /**
     * Get other licences table
     */
    protected function getOtherLicencesTable()
    {
        $id = $this->getFromRoute('id');
        $action = $this->getFromRoute('action');
        if ($action === 'edit-tm-application') {
            $method = 'getByTmApplicationId';
            $tableName = 'tm.otherlicences-applications';
        } else {
            $method = 'getByTmLicenceId';
            $tableName = 'tm.otherlicences-licences';
        }
        $data = $this->getServiceLocator()->get('Entity\OtherLicence')->$method($id);
        return $this->getServiceLocator()->get('Table')->prepareTable($tableName, $data);
    }

    /**
     * Process form and redirect back to list
     *
     * @param array $data
     * @param bool $showMessage
     * @return redirect
     */
    protected function processEditForm($data, $showMessage = true)
    {
        $action = $this->getFromRoute('action');

        if ($action == 'edit-tm-application') {
            $service = 'Entity\TransportManagerApplication';
            $message = 'The application has been updated';
            $type = 'app';
            $addIsOwner = true;
        } else {
            $service = 'Entity\TransportManagerLicence';
            $message = 'The licence has been updated';
            $type = 'lic';
            $addIsOwner = false;
        }

        $tmAppOrLicData = [
            'id' => $data['details']['id'],
            'version' => $data['details']['version'],
            'tmType' => $data['details']['tmType'],
            'additionalInformation' => $data['details']['additionalInformation'],
            'hoursMon' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursMon'],
            'hoursTue' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursTue'],
            'hoursWed' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursWed'],
            'hoursThu' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursThu'],
            'hoursFri' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursFri'],
            'hoursSat' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSat'],
            'hoursSun' => $data['details']['hoursOfWeek']['hoursPerWeekContent']['hoursSun'],
            'operatingCentres' => $data['details']['operatingCentres']
        ];
        if ($action == 'edit-tm-application') {
            $tmAppOrLicData['tmApplicationStatus'] = $data['details']['tmApplicationStatus'];
        }

        if ($addIsOwner) {
            $tmAppOrLicData['isOwner'] = $data['details']['isOwner'];
        }

        $this->getServiceLocator()->get($service)->save($tmAppOrLicData);

        if ($showMessage) {
            // @todo: There is a bug. Messages can't be displayed after the redirect. Need to fix in future stories.
            $this->flashMessenger()->addSuccessMessage($message);
        }

        return $this->redirectToIndex();
    }

    /**
     * PopulateEditForm
     *
     * @param Zend\Form\Form
     * @param array $data
     * @return Zend\Form\Form
     */
    private function populateEditForm($form, $data)
    {
        $action = $this->getFromRoute('action');

        $ocs = [];
        foreach ($data['operatingCentres'] as $oc) {
            $ocs[] = $oc['id'];
        }

        $dataPrepared = [
            'details' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'tmType' => $data['tmType']['id'],
                'additionalInformation' => $data['additionalInformation'],
                'operatingCentres' => $ocs,
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => $data['hoursMon'],
                        'hoursTue' => $data['hoursTue'],
                        'hoursWed' => $data['hoursWed'],
                        'hoursThu' => $data['hoursThu'],
                        'hoursFri' => $data['hoursFri'],
                        'hoursSat' => $data['hoursSat'],
                        'hoursSun' => $data['hoursSun'],
                    ]
                ]
            ]
        ];
        if ($action == 'edit-tm-application') {
            $dataPrepared['details']['tmApplicationStatus'] = $data['tmApplicationStatus']['id'];
        }

        if (isset($data['isOwner'])) {
            $dataPrepared['details']['isOwner'] = $data['isOwner'];
        }

        $form->setData($dataPrepared);

        return $form;
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
        return $this->deleteTmRecord('Entity\OtherLicence', $ids, $redirectAction, $recordId);
    }

    /**
     * Get required tm record id by othere licence id
     *
     * @param int $otherLicenceId
     * @return array
     */
    protected function getTmRecordId($otherLicenceId)
    {
        $record = $this->getServiceLocator()->get('Entity\OtherLicence')->getById($otherLicenceId);
        if (isset($record['transportManagerLicence']['id'])) {
            $key = 'transportManagerLicence';
        } else {
            $key = 'transportManagerApplication';
        }
        return $record[$key]['id'];
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
        if ($type == 'Add') {
            $redirectId = $this->fromRoute('id');
        } else {
            $redirectId = $this->getTmRecordId($this->fromRoute('id'));
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToAction($redirectAction, $redirectId);
        }

        $form = $this->getForm('TmOtherLicence');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId);
        }

        $this->formPost($form, 'processOtherLicenceForm');

        if ($this->getResponse()->getContent() !== "") {
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
     * @param string $type
     * @param string $redirectAction
     * @param int $redirectId
     * @return Zend\Form\Form
     */
    protected function populateOtherLicenceEditForm($form, $type, $redirectAction, $redirectId)
    {
        if ($type == 'Edit') {
            $otherLicence = $this->getServiceLocator()
                ->get('Entity\OtherLicence')
                ->getById($this->fromRoute('id'));
            $data['data'] = $otherLicence;
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
     * @return redirect
     */
    protected function processOtherLicenceForm($data)
    {
        $dataToSave = $data['data'];
        if ($data['data']['redirectAction'] == 'edit-tm-application') {
            $dataToSave['transportManagerApplication'] = $data['data']['redirectId'];
        } else {
            $dataToSave['transportManagerLicence'] = $data['data']['redirectId'];
        }
        $this->getServiceLocator()->get('Entity\OtherLicence')->save($dataToSave);

        return $this->redirectToAction($data['data']['redirectAction'], $data['data']['redirectId']);
    }

    /**
     * Redirect to given action
     *
     * @param string $action
     * @param int $id
     * @return redirect
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
}
