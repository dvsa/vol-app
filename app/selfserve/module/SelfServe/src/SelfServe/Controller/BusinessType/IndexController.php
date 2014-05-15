<?php

/**
 * Business Type Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace SelfServe\Controller\BusinessType;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Zend\Form\Element as FormElement;

/**
 * Business Type Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
{

    protected $messages;


    /**
     * Set current section
     */
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

        $formData = $this->getPersistedFormData();

        //configure number of elements if trading names collection
        $dynamicOptions = null;
        $tradingNamesArray = $formData[$this->getCurrentStep()]['trading_names'];

        if (!empty($tradingNamesArray)) {
            $dynamicOptions = ['trading_names_number' => (count($tradingNamesArray['trading_name']) + 1)];
        }

        // create form
        $form = $this->generateSectionForm($dynamicOptions);

        $form->get($this->getCurrentStep())->get('edit_business_type')->setValue($this->getUrlFromRoute(
            'selfserve/business-type',
            ['applicationId' => $applicationId]
        ));

        // pre fill form data if persisted
        if (isset($formData)) {
            $form->setData($formData);
        }

        // check for submit buttons
        $submitPosted = $this->determineSubmitButtonPressed($this->getRequest());

        $tradingNamesButton = $this->getRequest()->getPost(
            $this->getCurrentStep()
        )['trading_names']['submit_add_trading_name'];

        if (isset($tradingNamesButton)) {
            $submitPosted = 'add_trading_name';
        }

        // Do the post if required
        switch ($submitPosted) {
            case 'lookup_company':
                $form->setValidationGroup([$step => ['company_number']]);
                $form = $this->formPost($form, 'processLookupCompany', ['applicationId' => $applicationId]);
                break;
            case 'add_trading_name':
                $form->setValidationGroup([$step => ['trading_names' => ['trading_name']]]);
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

        return $this->renderLayoutWithSubSections(
            $view,
            $this->getCurrentStep(),
            'business-type',
            $businessStatus ? null : 'all'
        );
    }

    /**
     * Check if business type is set. if no redirects to page, where user can do this
     *
     * @return bool|Response
     */
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

    /**
     * Details action
     *
     * @return bool|mixed|Response
     */
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
                $forward = $this->forward()->dispatch('Selfserve\BusinessType\Index', [
                    'action' => 'generateStepForm',
                    'applicationId' => $applicationId,
                    'step' => $step,
                ]);
                break;
            }
        }

        return $forward;
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
     * @param array $validData
     * @param \Zend\Form $form
     */
    public function processBusinessType($validData, $form)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'organisationType' => $validData['business-type']['business-type'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getRegisteredCompanyFormData()
    {
        $licence = $this->getLicenceForBusiness();
        $organisation = $licence['organisation'];

        foreach ($licence['tradingNames'] as $tradingName) {
            $tradingNames[] = ['text' => $tradingName['tradingName']];
        }
        $tradingNames[] = ['text' => ''];

        return [
            'version' => $organisation['version'],
            'registered-company' => [
                'company_number' => $organisation['registeredCompanyNumber'],
                'company_name' => $organisation['name'],
                'type_of_business' => $organisation['sicCode'],
                'trading_names' => ['trading_name' => $tradingNames],
            ],
        ];
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $validData
     * @param \Zend\Form $form
     */
    public function processRegisteredCompany($validData, $form)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'name' => $validData['registered-company']['company_name'],
            'registeredCompanyNumber' => $validData['registered-company']['company_number'],
            'sicCode' => $validData['registered-company']['type_of_business'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
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
     * @param array $validData
     * @param \Zend\Form $form
     */
    public function processSoleTrader($validData, $form)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'sicCode' => $validData['sole-trader']['type_of_business'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
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
     * @param array $validData
     * @param \Zend\Form $form
     */
    public function processPartnership($validData, $form)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'name' => $validData['partnership']['company_name'],
            'sicCode' => $validData['partnership']['type_of_business'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
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
                'company_name' => $organisation['name'],
            //'trading_names' => $organisation['name'],
            ),
        );
    }

    /**
     * Method called as a callback once your business form has been validated.
     * Should redirect to the finance form page as the next step
     *
     * @param array $validData
     * @param \Zend\Form $form
     */
    public function processLlp($validData, $form)
    {
        $licence = $this->getLicenceEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $licence['id'],
            'registeredCompanyNumber' => $validData['llp']['company_number'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
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
     * @param array $validData
     * @param \Zend\Form $form
     * @param array $params
     */
    public function processOther($validData, $form, $params)
    {
        $licenceId = $params['licenceId'];
        $data = array(
            'id' => $licenceId,
            'name' => $validData['other']['company_name'],
            'sicCode' => $validData['other']['type_of_business'],
            'version' => $validData['version'],
        );

        $this->makeRestCall('LicenceOrganisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('licenceId' => $licenceId, 'step' => $nextStep)
        );
    }

    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to
     * indexAction.
     *
     * @param array $validData
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    protected function processLookupCompany($validData, $form, $params)
    {
        echo 'FORM VALID looking up company';
        exit;
    }

    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to
     * indexAction.
     *
     * @param array $validData
     * @param \Zend\Form $form
     * @param array $params
     */
    protected function processAddTradingName($validData, $form = null, $params = null)
    {
        $tNames = $validData[$this->getCurrentStep()]['trading_names']['trading_name'];
        foreach ($tNames as $key => $name) {

            //remove all elements
            $form->get($this->getCurrentStep())->get('trading_names')->get('trading_name')->remove($key);

            //remove empty itemes
            if (strlen(trim($name['text'])) == 0) {
                unset($tNames[$key]);
            }
        }

        $tNames[] = ['text' => ''];
        $tNames = array_merge($tNames);

        $validData[$this->getCurrentStep()]['trading_names']['trading_name'] = $tNames;

        $form->setData($validData);
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

    private function getLicenceForBusiness()
    {
        $applicationId = (int) $this->params()->fromRoute('applicationId');
        $bundle = [
            'children' => [
                'licence' => [
                    'children' => [
                        'organisation',
                        'tradingNames',
                    ]
                ],
            ],
        ];
        $application = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);
        return $application['licence'];
    }

    /**
     * Render the layout
     *
     * @param object $view
     * @param string $current
     * @param string $journey
     * @param mixed $disabled
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
