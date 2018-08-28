<?php

namespace Olcs\Controller\Traits;

use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Command\Permits\CreateFullPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\WithdrawEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
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
            $data['fields']['dateReceived'] = date("Y-m-d");
        }

        // Check to see if were editing a populated application
        if (!empty($application)) {
            $data = $this->prepareFormData($application);
        }

        // Set the numVehicles label and hidden fields on the form for validation
        $data['fields']['numVehicles'] = $licence['totAuthVehicles'];
        $data['fields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $permitForm->setData($data);


        // Instantiate view model and render the form
        $view = new ViewModel(['form' => $permitForm]);
        $this->loadScripts(['permits']);
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
        $data['fields']['countryIds'] = $application['countrys'];
        $data['fields']['status'] = $application['status']['id'];
        return ($data);
    }

    /**
     * Permits action
     *
     * @return \Zend\Http\Response
     * @throws UnexpectedResponseException
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


            // This was a From Action button being pressed, handle in helper method.
            if (array_key_exists('form-actions', $data)) {
                $formActionResult = $this->handleFormActions($data, $licence);
                if($formActionResult){
                    return $this->renderView($formActionResult);
                }
            }

            // Handles loading the a blank application form for case worker to populate
            if ($action === 'apply') {
                $applyView = $this->getCreateView($application, $licence);
                return $this->renderView($applyView);
            }

            // Handles loading a pre-populated form for an existing application.
            if ($action === 'edit') {
                if (!empty($data['id'])) {
                    $application = $this->getApplication($data['id']);
                }
                return $this->renderView($this->getCreateView($application, $licence));
            }
        }

        $view = $this->getPermitView();

        $issuedTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('issued-permits', []);

        $view->setVariable('issuedPermitTable', $issuedTable);
        $this->loadScripts(['permits']);
        $view->setTemplate('pages/permits/two-tables');

        return $this->renderView($view);
    }


    protected function handleFormActions($data, $licence)
    {

        $application = [];

        if (array_key_exists('save', $data['form-actions'])) {
            $form = $this->getForm('PermitCreate');
            $form->setData($data);
            if ($form->isValid()) {
                if (empty($data['fields']['id'])) {
                    $applicationData = $this->mapApplicationData($form->getData()['fields'], $licence['id']);
                    $command = CreateFullPermitApplication::create($applicationData);
                    $response = $this->handleCommand($command);
                    $this->checkResponse($response);
                } else {
                    $applicationData = $this->mapApplicationData($form->getData()['fields'], $licence['id']);
                    $command = UpdateEcmtPermitApplication::create($applicationData);
                    $response = $this->handleCommand($command);
                    $this->checkResponse($response);
                }
            } else {
                // Form didnt validate so re-render the form with errors highligted.
                $invalidFormView = $this->getCreateView($application, $licence, $form);
                return $invalidFormView;
            }
        }

        if (array_key_exists('withdraw', $data['form-actions'])) {
            $command = WithdrawEcmtPermitApplication::create(['id' => $data['fields']['id']]);
            $response = $this->handleCommand($command);
            $this->checkResponse($response);
        }

        if (array_key_exists('submit', $data['form-actions'])) {
            $command = EcmtSubmitApplication::create(['id' => $data['fields']['id']]);
            $response = $this->handleCommand($command);
            $this->checkResponse($response);
        }
        return false;
    }


    /**
     * Check command handler response
     *
     * @param $response
     * @return null
     * @throws UnexpectedResponseException
     */
    protected function checkResponse($response)
    {
        if (!$response->isOk()) {
            throw new UnexpectedResponseException('An error occurred saving the application');
        }
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

        // Remove any empty values
        foreach ($formFields as $key => $val) {
            if (empty($val) && !is_numeric($val)) {
                unset($formFields[$key]);
            }
        }
        if (empty($formFields['countryIds'])) {
            $formFields['countryIds'] = [];
        }
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
