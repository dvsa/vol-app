<?php

namespace Olcs\Controller\Traits;

use DateTime;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtApplicationByLicence;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList;
use Zend\View\Model\ViewModel;

/**
 * Permit Action Trait
 */
trait PermitActionTrait
{

    /**
     * Route (prefix) for permit action redirects
     *
     * @return string
     */
    protected abstract function getPermitRoute();

    /**
     * Route params for permit action redirects
     *
     * @return array
     */
    protected abstract function getPermitRouteParams();

    /**
     * Get view model for permit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected abstract function getPermitView();

    /**
     * Get configured permit form
     *
     * @return \Zend\Form\Form
     */
    protected abstract function getConfiguredPermitForm();

    protected $formHelper;

    /**
     * Get left view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(['form' => $this->getConfiguredPermitForm()]);
        $view->setTemplate('sections/permits/partials/left');

        return $view;
    }

    /**
     * Get create view form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getCreateView($application, $licence, $permitForm = null)
    {
        // Instantiate PermitCreate form
        if (!$permitForm) {
            $this->formHelper = $this->getServiceLocator()->get('Helper\Form');
            $permitForm = $this->formHelper->createForm('PermitCreate');
        }
        // Check to see if were editing a populated application
        if (!empty($application)) {
            // Call function to set the form values from the application
            $data = $this->prepareFormData($application);
        } else {
            //Just set the default date
            $data['fields']['dateReceived'] = date("Y-m-d");
        }

        // Set the numVehicles label and hidden fields on the form
        $data['fields']['numVehicles'] = $licence['totAuthVehicles'];
        $data['fields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $permitForm->setData($data);

        // Populate the Sectors list from the backend
        $permitForm->get('fields')
            ->get('sectors')
            ->setOptions($this->getSectorList());

        // Instantiate view model and render the form
        $view = new ViewModel(['form' => $permitForm]);
        $view->setTemplate('pages/form');
        return $view;
    }


    /**
     * prepareFormData helper method
     *
     * @return array
     */
    protected function prepareFormData($application)
    {

        // Ignore these array indexes on the application array when building from data array.
        $dontSet = ["permitType", 'licence', 'sectionCompletion', 'paymentStatus', 'status', 'confirmationSectionCompletion'];
        // Add necessary values to the array to re-populate the form.
        foreach ($application as $key => $value) {
            if (!in_array($key, $dontSet)) {
                $data['fields'][$key] = $application[$key];
            }
        }
        return ($data);
    }

    /**
     * Permits action
     *
     * @return \Zend\Http\Response
     */
    public function permitsAction()
    {
        // Setup some variables needed throughout
        $request = $this->getRequest();
        $licence = $this->getLicence((int)$this->params()->fromRoute('licence', null));

        if ($request->isPost()) {
            $action = strtolower($this->params()->fromPost('action'));
            $data = (array)$request->getPost();
            $application = [];

            // This block only triggered when user clicks "Save" on the form
            if (array_key_exists('form-actions', $data) && array_key_exists('save', $data['form-actions'])) {
                $form = $this->getForm('PermitCreate');
                $form->setData($data);
                if ($form->isValid()) {
                    if (empty($data['fields']['id'])) {
                        $applicationData = $this->mapApplicationData($form->getData()['fields'], $licence['id']);
                        $command = CreateEcmtPermitApplication::create($applicationData);
                        $response = $this->handleCommand($command);
                    } else {
                        $applicationData = $this->mapApplicationData($form->getData()['fields'], $licence['id']);
                        $command = UpdateEcmtPermitApplication::create($applicationData);
                        $response = $this->handleCommand($command);
                    }

                    // todo: refactor this when form is not being rendered into modal popup
                    $view = new ViewModel();
                    $saveMessage = in_array($response->getStatusCode(), [200, 201]) ? "Save Successful" : "Error saving form";
                    $view->setVariable('saveMessage', $saveMessage);
                    $view->setVariable('licenceId', $licence['id']);
                    $view->setTemplate('pages/permits/done');
                    return $this->renderView($view);
                } else {
                    // Form didnt validate so re-render the form with errors highligted.
                    $invalidFormView = $this->getCreateView($application, $licence, $form);
                    return $this->renderView($invalidFormView);
                }
            }

            // Handles loading the a blank application form for case worker to populate
            if ($action === 'apply') {
                $applyView = $this->getCreateView($application, $licence);
                return $this->renderView($applyView);
            }

            // Handles loading a pre-populated form for an existing application.
            if ($action === 'edit') {
                if (is_array($data['id']) && count($data['id'] == 1)) {
                    $application = $this->getApplication($data['id'][0]);
                }
                return $this->renderView($this->getCreateView($application, $licence));
            }


        }

        $view = $this->getPermitView();

        $issuedTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('issued-permits', []);

        $view->setVariable('issuedPermitTable', $issuedTable);
        $this->loadScripts(['permits', 'table-actions']);
        $view->setTemplate('pages/permits/two-tables');

        return $this->renderView($view);
    }


    /**
     * Executes query to retrieve list of Sectors to render on Create/Edit ECMT application form
     *
     * @return array
     */
    private function getSectorList()
    {
        $response = $this->handleQuery(SectorsList::create(array()));
        $sectorList = $response->getResult();

        $sectorList = $this->getServiceLocator()
            ->get('Helper\Form')
            ->transformListIntoValueOptions($sectorList, 'description');

        $sectorOtions['value_options'] = $sectorList;
        return $sectorOtions;
    }


    /**
     * Processes form data ready for use in Create/Update Command Handler
     *
     * @param $formFields array
     * @param $licenceId int
     * @return array
     */
    protected function mapApplicationData($formFields, $licenceId)
    {
        // Set licence as always needed
        $formFields['licence'] = $licenceId;

        // Get sector ID from POSTED id|message value
        if (!empty($formFields['sectors'])) {
            $formFields['sectors'] = substr($formFields['sectors'], 0, strpos($formFields['sectors'], '|'));
        }
        // Remove any empty values
        foreach ($formFields as $key => $val) {
            if (empty($val)) {
                unset($formFields[$key]);
            }
        }
        $formFields['fromInternal'] = true;
        return ($formFields);
    }



    /**
     * Returns an application entry by id
     *
     * @param $id
     * @return array
     */
    protected function getApplication($id)
    {
        $query = ById::create(['id' => $id]);
        $response = $this->handleQuery($query);

        return $response->getResult();
    }
}
