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
        $action = $this->getFromRoute('action');
        if ($action == 'edit-tm-application') {
            $service = 'Entity\TransportManagerApplication';
            $method = 'getTransportManagerApplication';
            $key = 'application';
        } else {
            $service = 'Entity\TransportManagerLicence';
            $method = 'getTransportManagerLicence';
            $key = 'licence';
        }

        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');

        $data = $this->getServiceLocator()->get($service)->$method($id);

        return $this->getServiceLocator()->get('Entity\TransportManager')
            ->getDocuments(
                $tmId,
                $data[$key]['id'],
                $key,
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

        if ($action == 'edit-tm-application') {
            $service = 'Entity\TransportManagerApplication';
            $method = 'getTransportManagerApplication';
            $key = 'application';
        } else {
            $service = 'Entity\TransportManagerLicence';
            $method = 'getTransportManagerLicence';
            $key = 'licence';
        }

        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');

        $data = $this->getServiceLocator()->get($service)->$method($id);

        return $this->uploadFile(
            $file,
            array(
                'transportManager' => $tmId,
                $key => $data[$key]['id'],
                'description' => 'Additional information',
                'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
            )
        );
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

        $licenceOcService = $this->getServiceLocator()->get('Entity\LicenceOperatingCentre');
        $tmApplicationOcService = $this->getServiceLocator()->get('Olcs\Service\Data\ApplicationOperatingCentre');

        $tmApplicationOcService->setApplicationId($tmAppData['application']['id']);
        $tmApplicationOcService->setLicenceId($tmAppData['application']['licence']['id']);
        $tmApplicationOcService->setLicenceOperatingCentreService($licenceOcService);

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
            $form->setData((array)$request->getPost());

            if (!$processed) {
                $this->formPost($form, 'processEditForm');
                if ($this->getResponse()->getContent() !== '') {
                    return $this->getResponse();
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
            $form->setData($request->getPost());

            if (!$processed) {
                $this->formPost($form, 'processEditForm');
                if ($this->getResponse()->getContent() !== '') {
                    return $this->getResponse();
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

        return $this->renderView($view, 'Edit licence');
    }

    /**
     * Delete TM application or licence
     *
     * @param string $serviceName
     * @return Redirect
     */
    protected function deleteTmRecord($serviceName)
    {
        $id = $this->getFromRoute('id');

        $response = $this->confirm('Are you sure you want to permanently delete this record?');

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        if (!$this->isButtonPressed('cancel')) {
            $this->getServiceLocator()->get($serviceName)->delete($id);
            $this->addSuccessMessage('Deleted successfully');
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

        $transportManagerApplication = [
            'application' => $data['details']['application'],
            'transportManager' => $tm,
            'action' => 'A'
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
    protected function alterEditForm($form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $ocElement = $form->get('details')->get('operatingCentres');

        $action = $this->getFromRoute('action');
        if ($action == 'edit-tm-licence') {
            $service = $this->getServiceLocator()->get('Common\Service\Data\LicenceOperatingCentre');
        } else {
            $service = $this->getServiceLocator()->get('Olcs\Service\Data\ApplicationOperatingCentre');
        }

        $options = $service->fetchListOptions([]);
        $ocElement->setValueOptions($options);

        $formHelper->removeOption($form->get('details')->get('tmType'), 'tm_t_B');

        return $form;
    }

    /**
     * Process form and redirect back to list
     *
     * @param array $data
     * @return redirect
     */
    protected function processEditForm($data)
    {
        $action = $this->getFromRoute('action');

        if ($action == 'edit-tm-application') {
            $service = 'Entity\TransportManagerApplication';
            $message = 'The application has been updated';
            $type = 'app';
        } else {
            $service = 'Entity\TransportManagerLicence';
            $message = 'The licence has been updated';
            $type = 'lic';
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

        $this->getServiceLocator()->get($service)->save($tmAppOrLicData);

        // @todo: There is a bug. Messages can't be displayed after the redirect. Need to fix in future stories.
        $this->flashMessenger()->addSuccessMessage($message);

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

        $form->setData($dataPrepared);

        return $form;
    }
}
