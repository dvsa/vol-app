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
     * Organisation
     *
     * @var array
     */
    protected $organisation;

    /**
     * Licence
     *
     * @var array
     */
    protected $licence;


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
        $section = $this->getCurrentSection();
        $this->setCurrentStep($step);

        $businessStatus = $this->checkBusinessType();

        if ($businessStatus instanceof Response) {
            return $businessStatus;
        }

        $formGenerator = $this->getFormGenerator();

        // get initial form
        $stepFormConfig = $formGenerator->getFormConfig($section);
        if (isset($stepFormConfig[$section]['fieldsets'])) {
            $stepFormConfig[$section] = $formGenerator->addFieldset(
                $stepFormConfig[$section],
                $this->determineFormFieldset()
            );
        }

        // set form config on formGenerator
        $formGenerator->setFormConfig($stepFormConfig);

        // create form
        $form = $formGenerator->createForm($section);

        if ($step == 'details') {
            $form->get($this->determineFormFieldset())->get('edit_business_type')->setValue($this->getUrlFromRoute(
                'selfserve/business-type',
                ['applicationId' => $applicationId]
            ));
        }


        // pre fill form data if persisted
        $formData = $this->getPersistedFormData();
        if (isset($formData)) {
            $form->setData($formData);
        }



        // check for submit buttons
        $submitPosted = $this->determineSubmitButtonPressed($this->getRequest());

        $tradingNamesButton = $this->getRequest()->getPost(
            $this->determineFormFieldset()
        );

        if (isset($tradingNamesButton['trading_names']['submit_add_trading_name'])) {
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
        $view = new ViewModel(
            ['form' => $form,
            'completionStatus' => count($completionStatus['Results']) ? $completionStatus['Results'][0] : null,
            'applicationId' => $applicationId]
        );
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
        $organisation = $this->getOrganisationEntity();
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $organisation['id'],
            'organisationType' => $validData['business-type']['business-type'],
            'version' => $organisation['version'],
        );

        $this->makeRestCall('Organisation', 'PUT', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            ['applicationId' => $applicationId, 'step' => $nextStep]
        );
    }

    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getDetailsFormData()
    {

        $licence = $this->getLicenceForBusiness();
        $organisation = $licence['organisation'];

        foreach ($licence['tradingNames'] as $tradingName) {
            $tradingNames[] = ['text' => $tradingName['tradingName']];
        }
        $tradingNames[] = ['text' => ''];

        return [
            'version' => $organisation['version'],
            $this->determineFormFieldset() => [
                'business_type' => $organisation['organisationType'],
                'company_number' => ['company_number' => $organisation['registeredCompanyNumber']],
                'company_name' => $organisation['name'],
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
    public function processDetails($validData, $form)
    {
        $licence = $this->getLicenceForBusiness();
        $applicationId = $this->getApplicationId();
        $currentData = $validData[$this->determineFormFieldset()];

        $data = [
            'id' => $licence['organisation']['id'],
            'name' => isset($currentData['company_name']) ? $currentData['company_name'] : null,
            'registeredCompanyNumber' => isset($currentData['company_number'])
                    ? $currentData['company_number']['company_number']
                    : null,
            'version' => $licence['organisation']['version'],
        ];

        //if field is not present in form, then do not update it within entity
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset ($data[$key]);
            }
        }

        //if trading names are present
        if ($currentData['trading_names']) {

            $validData = $this->processAddTradingName($validData, $form, null, false);
            $currentData = $validData[$this->determineFormFieldset()];

            $tradingNames = array();
            foreach ($currentData['trading_names']['trading_name'] as $tradingName) {
                $tradingNames[] = [
                    'tradingName' => $tradingName['text'],
                    'licence' => $licence['id'],
                ];
            }

            $this->makeRestCall('TradingNames', 'POST', $tradingNames);

        }

        $this->makeRestCall('Organisation', 'PATCH', $data);

        $nextStep = $this->evaluateNextStep($form);
        $this->redirect()->toRoute(
            'selfserve/business-type',
            array('applicationId' => $applicationId, 'step' => $nextStep)
        );
    }

    /**
     * Method called once a valid company look up form has been submitted.
     *
     * @param array $validData
     * @param \Zend\Form $form
     * @param array $journeyData
     */
    protected function processLookupCompany($validData, $form)
    {
        if (array_key_exists('registered-company', $validData)) {
            $key = 'registered-company';
        } elseif (array_key_exists('llp', $validData)) {
            $key = 'llp';
        } else {
            return $form;
        }
        $result = $this->makeRestCall(
            'CompaniesHouse', 'GET', array(
            'type' => 'numberSearch', 'value' => $validData[$key]['company_number'])
        );
        if ($result['Count'] == 1) {
            $companyName = $result['Results'][0]['CompanyName'];
            $form->get($key)->get('company_name')->setValue($companyName);
            return $form;
        } else {
            $form->get($key)->get('company_number')->setMessages(
                array('companyNumber' => array(
                    'Sorry, we couldn\'t find any matching companies, '
                    . 'please try again or enter your details manually below'))
            );
        }
    }

    /**
     * Method called once a valid company look up form has been submitted.
     * Needs to call CH Controller and implement PRG and redirect back to
     * indexAction.
     *
     * @param array $validData
     * @param \Zend\Form $form
     * @param array $params
     * @return array $validData
     */
    protected function processAddTradingName($validData, $form = null, $params = null, $emptyEntryOnBottom = true)
    {
        $tNames = $validData[$this->determineFormFieldset()]['trading_names']['trading_name'];
        foreach ($tNames as $key => $name) {

            //remove all elements
            $form->get($this->determineFormFieldset())->get('trading_names')->get('trading_name')->remove($key);

            //remove empty items
            if (strlen(trim($name['text'])) == 0) {
                unset($tNames[$key]);
            }
        }

        //add empty entry on bottom of collection
        if ($emptyEntryOnBottom) {
            $tNames[] = ['text' => ''];
        }

        $tNames = array_merge($tNames);

        $validData[$this->determineFormFieldset()]['trading_names']['trading_name'] = $tNames;

        $form->setData($validData);
        return $validData;
    }

    /**
     * Get organisation entity based on applicationId
     *
     * @return array
     */
    private function getOrganisationEntity()
    {
        if (empty($this->organisation)) {

            $applicationId = $this->getApplicationId();

            $bundle = array(
                'children' => array(
                    'licence' => array(
                        'children' => array('organisation')
                    ),
                ),
            );

            $application = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);
            $this->organisation = $application['licence']['organisation'];

        }

        return $this->organisation;
    }

    private function getLicenceForBusiness()
    {
        if (empty($this->licence)) {
            $applicationId = $this->getApplicationId();
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
            $this->licence = $application['licence'];
        }
        return $this->licence;
    }

    private function determineFormFieldset()
    {
        $organisation = $this->getOrganisationEntity();

        if ($this->getCurrentStep() == 'details') {

            switch ($organisation['organisationType']) {
                case 'org_type.lc':
                    return 'registered-company';
                    break;
                case 'org_type.st':
                    return 'sole-trader';
                    break;
                case 'org_type.p':
                    return 'partnership';
                    break;
                case 'org_type.llp':
                    return 'llp';
                    break;
                case 'org_type.o':
                    return 'other';
                    break;
                default:
                    return $this->getCurrentStep();
                    break;
            }
        }

        return $this->getCurrentStep();
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
                    'route' => 'selfserve/business-type',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                        'step' => 'details',
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
