<?php

/**
 * Transport Manager Details Responsibility Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;

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
            if ($response instanceof \Zend\Http\Response) {
                return $response;
            }
        }

        $applicationsTable = $this->getApplicationsTable();
        $licencesTable = $this->getLicencesTable();

        $view = $this->getViewWithTm(
            ['applicationsTable' => $applicationsTable->render(), 'licencesTable' => $licencesTable->render()]
        );
        $view->setTemplate('pages/transport-manager/tm-responsibility');
        return $this->renderView($view);
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

        $table = $this->getTable(
            'tm.applications',
            $results
        );
        return $table;
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

        $table = $this->getTable(
            'tm.licences',
            $results
        );
        return $table;
    }

    /**
     * Add TM application action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $form = $this->getForm('transport-manager-application-small');
        $request = $this->getRequest();
        if ($request->isPost()) {

            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

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
        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }
        return $this->renderView($view, 'Add application');
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

        $appData = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getDataForProcessing($data['details']['application']);

        $transportManagerApplication = [
            'application' => $data['details']['application'],
            'transportManager' => $tm,
            'tmApplicationStatus' => $appData['status']['id'],
            'action' => 'A'
        ];
        $result = $this->getServiceLocator()
            ->get('Entity\TransportManagerApplication')
            ->save($transportManagerApplication);

        $routeParams['id'] = $result['id'];

        return $this->redirectToRoute('transport-manager/details/responsibilities', $routeParams);
    }

    /**
     * Edit TM application action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editTmApplicationAction()
    {
        $serviceLocator =  $this->getServiceLocator();
        $titleFlag = $this->getFromRoute('title', 0);
        $tmAppId = $this->getFromRoute('id');
        $title = $titleFlag ? 'Add application' : 'Edit application';

        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $tmAppData = $serviceLocator
            ->get('Entity\TransportManagerApplication')
            ->getTransportManagerApplication($tmAppId);

        $tmApplicationOcService = $serviceLocator->get('Olcs\Service\Data\ApplicationOperatingCentre');
        $tmApplicationOcService->setApplicationId($tmAppData['application']['id']);
        $tmApplicationOcService->setLicenceId($tmAppData['application']['licence']['id']);

        $licenceOcService = $serviceLocator->get('Entity\LicenceOperatingCentre');
        $tmApplicationOcService->setLicenceOperatingCentreService($licenceOcService);

        $form = $this->alterEditForm($this->getForm('transport-manager-application-full'));

        $uploaded = $this->processFiles(
            $form,
            'details->file',
            array($this, 'processAdditionalInformationFileUpload'),
            array($this, 'deleteTmFile'),
            array($this, 'getDocuments')
        );

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateEditForm($form, $tmAppData);
        }
        if (!$uploaded) {
            $this->formPost($form, 'processEditForm');
            if ($this->getResponse()->getContent() !== "") {
                return $this->getResponse();
            }
        } else {
            $form->setData($this->getRequest()->getPost());
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
     * Alter edit form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterEditForm($form)
    {
        $tmType = $form->get('details')->get('tmType');
        $valueOptions = $tmType->getValueOptions();
        unset($valueOptions['tm_t_B']);
        $tmType->setValueOptions($valueOptions);
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
        $this->saveTmApplicationOcs($data);

        $tmAppData = [
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
        ];
        $this->getServiceLocator()->get('Entity\TransportManagerApplication')->save($tmAppData);

        // @todo: There is a bug. Messages can't be displayed after the redirect. Need to fix in future stories.
        $this->flashMessenger()->addSuccessMessage('The application has been updated');
        return $this->redirectToIndex();
    }

    /**
     * Save TM application OCs
     *
     * @param array $data
     */
    protected function saveTmApplicationOcs($data)
    {
        $tmAppId = $this->fromRoute('id');
        $tmApplicationOcs = $data['details']['tmApplicationOc'] ? $data['details']['tmApplicationOc'] : [];
        $tmAppOcService = $this->getServiceLocator()->get('Entity\TmApplicationOperatingCentre');
        $existingRecords = $tmAppOcService->getAllForTmApplication($tmAppId);
        $formattedExistingRecords = [];
        foreach ($existingRecords['Results'] as $record) {
            $formattedExistingRecords[] = $record['operatingCentre']['id'];
        }

        $recordsToInsert = array_diff($tmApplicationOcs, $formattedExistingRecords);
        $recordsToDelete = array_diff($formattedExistingRecords, $tmApplicationOcs);

        $tmAppOcService->deleteByTmAppAndIds($tmAppId, $recordsToDelete);

        foreach ($recordsToInsert as $ocId) {
            $tmAppOcData = [
                'transportManagerApplication' => $tmAppId,
                'operatingCentre' => $ocId
            ];
            $tmAppOcService->save($tmAppOcData);
        }
    }

    /**
     * PopulateEditForm
     *
     * @param Zend\Form\Form
     * @param array $tmAppData
     * @return Zend\Form\Form
     */
    private function populateEditForm($form, $tmAppData)
    {
        $oc = [];
        foreach ($tmAppData['tmApplicationOcs'] as $appOc) {
            $oc[] = $appOc['operatingCentre']['id'];
        }
        $data = [
            'details' => [
                'id' => $tmAppData['id'],
                'version' => $tmAppData['version'],
                'tmType' => $tmAppData['tmType']['id'],
                'additionalInformation' => $tmAppData['additionalInformation'],
                'tmApplicationOc' => $oc,
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => $tmAppData['hoursMon'],
                        'hoursTue' => $tmAppData['hoursTue'],
                        'hoursWed' => $tmAppData['hoursWed'],
                        'hoursThu' => $tmAppData['hoursThu'],
                        'hoursFri' => $tmAppData['hoursFri'],
                        'hoursSat' => $tmAppData['hoursSat'],
                        'hoursSun' => $tmAppData['hoursSun'],
                    ]
                ]
            ]
        ];
        $form->setData($data);
        return $form;
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
        $appData = $this->getServiceLocator()
            ->get('Entity\TransportManagerApplication')
            ->getTransportManagerApplication($id);
        return $this->getServiceLocator()->get('Entity\TransportManager')
            ->getDocuments(
                $tmId,
                $appData['application']['id'],
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
        $tmId = $this->getFromRoute('transportManager');
        $id = $this->getFromRoute('id');
        $appData = $this->getServiceLocator()
            ->get('Entity\TransportManagerApplication')
            ->getTransportManagerApplication($id);
        return $this->uploadFile(
            $file,
            array(
                'transportManager' => $tmId,
                'application' => $appData['application']['id'],
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
        return $this->deleteTmRecord('Entity\TransportManagerApplication', 'Entity\TmApplicationOperatingCentre');
    }

    /**
     * Delete TM application or licence
     * 
     * @param string $serviceName
     * @param string $childServiceName
     * @return Redirect
     */
    protected function deleteTmRecord($serviceName, $childServiceName)
    {
        $methods = [
            'Entity\TmApplicationOperatingCentre' => 'deleteByTmApplication',
            'Entity\TmLicenceOc' => 'deleteByTmLicence'
        ];
        $id = $this->getFromRoute('id');
        $response = $this->confirm(
            'Are you sure you want to permanently delete this record?'
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }
        if (!$this->isButtonPressed('cancel')) {
            $this->getServiceLocator()->get($serviceName)->delete($id);
            $this->getServiceLocator()->get($childServiceName)->$methods[$childServiceName]($id);
            $this->addSuccessMessage('Deleted successfully');
        }
        return $this->redirectToIndex();
    }
}
