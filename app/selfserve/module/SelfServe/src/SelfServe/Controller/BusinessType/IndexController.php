<?php

/**
 * Business Type Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\BusinessType;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
/**
 * Business Type Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
{

    protected $messages;

    public function __construct()
    {
        $this->setCurrentSection('business-type');
    }

    /**
     * Main method of processing the form. Generates a form and if a submit
     * button is pressed, sets the validation group based on that button AND
     * defines which callback should be used if the form is valid.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function generateStepFormAction()
    {

        $applicationId = $this->params()->fromRoute('applicationId');
        $step = $this->params()->fromRoute('step');
        $this->setCurrentStep($step);

        $businessStatus = $this->checkBusinessType();

        if ($businessStatus instanceof Response) {
            return $businessStatus;
        }

        // create form
        $form = $this->generateSectionForm();

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);

        if (isset($formData)) {
            $form->setData($formData);
        }

        // check for submit buttons
        $submit_posted = $this->determineSubmitButtonPressed($this->getRequest());

        // Do the post if required
        switch ($submit_posted) {
            case 'lookup_company':
                $form->setValidationGroup([$step => ['company_number']]);
                $form = $this->formPost($form, 'processLookupCompany', ['applicationId' => $applicationId]);
                break;
            case 'add_trading_name':
                $form->setValidationGroup([$step => ['trading_names']]);
                $form = $this->formPost($form, 'processAddTradingName', ['applicationId' => $applicationId]);
                break;
            default:
                $form = $this->formPost(
                    $form,
                    $this->getStepProcessMethod($this->getCurrentStep()),
                    ['applicationId' => $applicationId]
                );
                break;
        }

        // collect completion status
        $completionStatus = $this->makeRestCall(
            'ApplicationCompletion',
            'GET',
            array('application_id' => $applicationId)
        );

        // render the view
        $view = new ViewModel(['form' => $form,
                                'completionStatus' => $completionStatus['Results'][0],
                                'applicationId' => $applicationId]);
        $view->setTemplate('self-serve/business/index');

        $organisation = $this->getOrganisationEntity();

        return $this->renderLayoutWithSubSections(
            $view,
            $this->getCurrentStep(),
            'business-type',
            $businessStatus ? null : 'all'
        );
    }

    public function checkBusinessType()
    {
        $organisation = $this->getOrganisationEntity();
        $applicationId = $this->getApplicationId();

        //redirect to business type if was not yet set
        if ($this->getCurrentStep() != 'business-type' && empty($organisation['organisationType'])) {
            return $this->redirect()->toRoute('selfserve/business-type', ['applicationId' => $applicationId]);
        }

        return !empty($organisation['organisationType']);
    }

    public function detailsAction()
    {
        $businessStatus = $this->checkBusinessType();
        if ($businessStatus instanceof Response) {
            return $businessStatus;
        }

        $applicationId = $this->getApplicationId();
        $organisation = $this->getOrganisationEntity();

        $mainStep = 'business-type';
        $this->setCurrentStep($mainStep);
        $form = $this->generateSectionForm();

        $valueStepPairs = $form->get($mainStep)->getOptions()['next_step']['values'];

        foreach ($valueStepPairs as $val => $step) {

            //redirect to correct step
            if ($val == $organisation['organisationType']) {
                return $this->forward()->dispatch('Selfserve\BusinessType\Index', [
                    'action' => 'generateStepForm',
                    'applicationId' => $applicationId,
                    'step' => $step,
                ]);
            }
        }

        //no value has been found. redirect to business type choice
        return $this->redirect()->toRoute('selfserve/business-type', ['applicationId' => $applicationId]);
    }

    /**
     * End of the journey redirect to finance section
     *
     */
    public function completeAction()
    {
        $applicationId = $this->params('applicationId');
        $this->redirect()->toRoute('selfserve/finance/operating_centre', ['applicationId' => $applicationId]);
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getBusinessTypeFormData()
    {
        $organisation = $this->getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'business-type' => array(
                'business-type' => $organisation['organisationType'],
            ),
        );
    }

    /**
     * Method called as a callback once business type form has been validated.
     * Should redirect to the correct business type form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processBusinessType($valid_data, $form, $params)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'organisationType' => $valid_data['business-type']['business-type'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $next_step)
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getRegisteredCompanyFormData()
    {
        $organisation = $this->getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'registered-company' => array(
                'company_number' => $organisation['registeredCompanyNumber'],
                'company_name' => $organisation['name'],
                'type_of_business' => $organisation['sicCode'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processRegisteredCompany($valid_data, $form, $params)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'name' => $valid_data['registered-company']['company_name'],
            'registeredCompanyNumber' => $valid_data['registered-company']['company_number'],
            'sicCode' => $valid_data['registered-company']['type_of_business'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $next_step)
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getSoleTraderFormData()
    {
        $organisation = $this->getOrganisationEntity();
        return array(
            'version' => $organisation['version'],
            'sole-trader' => array(
                'type_of_business' => $organisation['sicCode'],
            //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processSoleTrader($valid_data, $form, $params)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'sicCode' => $valid_data['sole-trader']['type_of_business'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $next_step)
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getPartnershipFormData()
    {
        $organisation = $this->getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'partnership' => array(
                'company_name' => $organisation['name'],
                'type_of_business' => $organisation['sicCode'],
            //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processPartnership($valid_data, $form, $params)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'name' => $valid_data['partnership']['company_name'],
            'sicCode' => $valid_data['partnership']['type_of_business'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $next_step)
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLlpFormData()
    {
        $organisation = $this->getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'llp' => array(
                'company_number' => $organisation['registeredCompanyNumber'],
            //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLlp($valid_data, $form, $params)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'registeredCompanyNumber' => $valid_data['llp']['company_number'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $next_step)
        );
    }


    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getOtherFormData()
    {
        $organisation = $this->getOrganisationEntity();

        return array(
            'version' => $organisation['version'],
            'other' => array(
                'company_name' => $organisation['name'],
                'type_of_business' => $organisation['sicCode'],
            //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOther($valid_data, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
            'id' => $licenceId,
            'name' => $valid_data['other']['company_name'],
            'sicCode' => $valid_data['other']['type_of_business'],
            'version' => $valid_data['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('licenceId' => $licenceId, 'step' => $next_step)
        );
    }

    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to
     * indexAction.
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processLookupCompany($valid_data, $form, $params)
    {
        echo 'FORM VALID looking up company';
        exit;
    }

    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to
     * indexAction.
     *
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processAddTradingName($valid_data, $form, $params)
    {
        echo 'FORM VALID adding trading name';

        exit;
    }

    /**
     * Get organisation entity based on applicationId
     *
     * @return array
     */
    private function getOrganisationEntity()
    {
        $applicationId = (int) $this->params()->fromRoute('applicationId');

        $bundle = array(
            'children' => array(
                'licence' => array(
                    'children' => array('organisation')
                ),
            ),
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);
        return $application['licence']['organisation'];
    }

    /**
     * Render the layout
     *
     * @param object $view
     * @param string $current
     * @param string $journey
     * @return ViewModel
     */
    public function renderLayoutWithSubSections($view, $current = '', $journey = 'business-type', $disabled = null)
    {
        $applicationId = $this->getApplicationId();

        $this->setSubSections(
            array(
                'business-details' => array(
                    'label' => 'selfserve-app-subSection-business-details',
                    'route' => 'selfserve/business-details',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                    )
                ),
                'addresses' => array(
                    'label' => 'selfserve-app-subSection-business-addresses',
                    'route' => 'selfserve/business-type',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                        'step' => 'addresses',
                    )
                ),
                'people' => array(
                    'label' => 'selfserve-app-subSection-business-people',
                    'route' => 'selfserve/business-type',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                        'step' => 'people',
                    )
                ),
            )
        );

        $subSections = $this->getSubSections();
        if ($current != 'business-type' && !array_key_exists($current, $subSections)) {
            reset($subSections);
            $current = key($subSections);
        }

        return parent::renderLayoutWithSubSections($view, $current, $journey, $disabled);
    }
}
