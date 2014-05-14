<?php

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller;

use Common\Controller\FormActionController;
use Zend\Http\Response;

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractJourneyController extends FormActionController
{
    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = null;

    /**
     * Holds the sub action service name
     *
     * @var string
     */
    protected $subActionService = null;

    /**
     * Holds the action name
     *
     * @var string
     */
    private $action;

    /**
     * Hold the journey name
     *
     * @var string
     */
    private $journeyName;

    /**
     * Hold the section name
     *
     * @var string
     */
    private $sectionName;

    /**
     * Hold the sub section name
     *
     * @var string
     */
    private $subSectionName;

    /**
     * Hold the journey config
     *
     * @var array
     */
    private $journeyConfig = array();

    /**
     * Holds the section reference
     *
     * @var string
     */
    private $sectionReference;

    /**
     * Holds the table name
     *
     * @var string
     */
    private $tableName;

    /**
     * Holds the form name
     *
     * @var string
     */
    private $formName;

    /**
     * Holds the identifier
     *
     * @var int
     */
    private $identifier;

    /**
     * Holds the section completion
     *
     * @var array
     */
    private $sectionCompletion;

    /**
     * Holds the steps
     *
     * @var array
     */
    private $steps = array();

    /**
     * Holds the step number
     *
     * @var int
     */
    private $stepNumber;

    /**
     * Holds the access keys
     *
     * @var array
     */
    protected $accessKeys;

    /**
     * Generic index action
     */
    public function indexAction()
    {
        return $this->goToFirstSection();
    }

    /**
     * Render the section
     *
     * @return Response
     */
    protected function renderSection($view = null, $params = array())
    {
        $journeyName = $this->getJourneyName();
        $sectionName = $this->getSectionName();
        $subSectionName = $this->getSubSectionName();

        if (!$this->isSectionAccessible($journeyName, $sectionName, null)
            || !$this->isSectionAccessible($journeyName, $sectionName, $subSectionName)) {
            return $this->goToNextStep();
        }

        if (!$this->isSectionEnabled($journeyName, $sectionName, null)
            || !$this->isSectionEnabled($journeyName, $sectionName, $subSectionName)
            || $this->isButtonPressed('back')) {
            return $this->goToPreviousStep();
        }

        if (empty($view)) {
            $view = $this->getViewModel($params);
        }

        if ($this->isSubAction()) {
            $view->setVariable('title', $this->getSectionReference());
        }

        if ($view->getTemplate() == null) {
            $view->setTemplate('self-serve/journey/' . strtolower($journeyName) . '/main');
        }

        if ($this->hasTable()) {

            $action = $this->checkForCrudAction();

            if ($action instanceof Response) {
                return $action;
            }

            $tableName = $this->getTableName();

            $data = $this->getTableData($this->getIdentifier());

            $settings = $this->getTableSettings();

            $table = $this->alertTable($this->getTable($tableName, $data, $settings));

            $view->setVariable('table', $table->render());
        }

        if ($this->hasForm()) {

            if ($this->isSubAction() && $this->isButtonPressed('cancel')) {
                return $this->goBackToSection();
            }

            $formName = $this->getFormName();

            $callback = 'processSave';

            $data = array();

            if ($this->isSubAction()) {
                $callback = 'processSubActionSave';

                $action = $this->getAction();

                if ($action === 'edit') {
                    $id = $this->params()->fromRoute('id');

                    $data = $this->loadSubSection($id);

                    if ($data instanceof Response) {
                        return $data;
                    }

                    $data = $this->processSubSectionLoad($data);
                }

            } else {
                $data = $this->processLoad($this->load($this->getIdentifier()));
            }

            $form = $this->generateFormWithData($formName, $callback, $data);

            if ($this->getStepNumber() == 0) {
                $form->get('form-actions')->remove('back');
            }

            if ($this->isSubAction() && $this->getAction() == 'edit') {
                $form->get('form-actions')->remove('addAnother');
            }

            if ($form instanceof Response) {
                return $form;
            }

            $view->setVariable('form', $this->alterForm($form));
        }

        if ($this->hasView()) {
            $view->setTemplate($this->getViewName());
        }

        return $this->render($view);
    }

    /**
     * Redirect to sub section
     *
     * @return Response
     */
    protected function goBackToSection()
    {
        $route = $this->getSectionRoute(
            $this->getJourneyName(),
            $this->getSectionName(),
            $this->getSubSectionName()
        );

        return $this->goToSection(
            $route,
            array($this->getJourneyConfig()['identifier'] => $this->getIdentifier()),
            false
        );
    }

    /**
     * Go back to sub action
     *
     * @return Response
     */
    protected function goBackToAddAnother()
    {
        $route = $this->getSectionRoute(
            $this->getJourneyName(),
            $this->getSectionName(),
            $this->getSubSectionName()
        );

        return $this->goToSection($route);
    }

    /**
     * Check if we have a sub action
     *
     * @return boolean
     */
    protected function isSubAction()
    {
        $action = $this->getAction();

        return ($action != 'index');
    }

    /**
     * Getter for action
     *
     * @return string
     */
    protected function getAction()
    {
        if (empty($this->action)) {
            $this->action = $this->params()->fromRoute('action');
        }

        return $this->action;
    }

    /**
     * Get table data
     *
     * This method should be overridden
     *
     * @return array
     */
    protected function getTableData($id)
    {
        return array();
    }

    /**
     * Get table settings
     *
     * This method should be overridden
     *
     * @return array
     */
    protected function getTableSettings()
    {
        return array();
    }

    /**
     * Alter table
     *
     * This method should be overridden
     *
     * @param object $table
     * @return object
     */
    protected function alertTable($table)
    {
        return $table;
    }

    /**
     * Render the view
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function render($view)
    {
        $navigation = $this->getNavigationView();

        $layout = $this->getViewModel(
            array(
                'subSections' => $this->getSubSectionsForLayout(),
                'id' => $this->getIdentifier()
            )
        );

        $layout->setTemplate('self-serve/journey/' . strtolower($this->getJourneyName()) . '/layout');

        $layout->addChild($view, 'main');

        $layout->addChild($navigation, 'navigation');

        return $layout;
    }

    /**
     * Alter the form
     *
     * This method should be overridden
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        return $form;
    }

    /**
     * Load sub section data
     *
     * @param int $id
     * @return array
     */
    protected function loadSubSection($id)
    {
        $result = $this->makeRestCall($this->getSubActionService(), 'GET', array('id' => $id));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        return $result;
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        return array();
    }

    /**
     * Process loading the sub section data
     *
     * @param array $data
     * @return array
     */
    protected function processSubSectionLoad($data)
    {
        return $data;
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        return $data;
    }

    /**
     * Add operating centre
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit operating centre
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    protected function deleteAction()
    {
        if (!empty($this->getSubActionService()) && !empty($this->getSubActionId())) {
            $this->makeRestCall($this->getSubActionService(), 'DELETE', array('id' => $this->getSubActionId()));
            return $this->goBackToSection();
        }

        return $this->notFoundAction();
    }

    /**
     * Get the sub action service
     *
     * @return string
     */
    protected function getSubActionService()
    {
        return $this->subActionService;
    }

    /**
     * Get the service name
     *
     * @return string
     */
    protected function getService()
    {
        return $this->service;
    }

    /**
     * Get the sub action id
     *
     * @return int
     */
    protected function getSubActionId()
    {
        return $this->params()->fromRoute('id');
    }

    /**
     * Save sub action data
     *
     * @param array $data
     */
    protected function saveSubAction($data)
    {
        $method = 'PUT';

        if (isset($data['data']['id'])) {
            $method = 'POST';
        }

        $this->makeRestCall($this->getSubActionService(), $method, $data['data']);

        if ($this->isButtonPressed('addAnother')) {
            return $this->goBackToAddAnother();
        }

        return $this->goBackToSection();
    }

    /**
     * Save data
     *
     * @param array $data
     */
    protected function save($data)
    {
        $this->makeRestCall($this->getService(), 'PUT', $data['data']);

        return $this->goToNextStep();
    }

    /**
     * Save the sub action
     *
     * @param array $data
     * @return array
     */
    protected function processSubActionSave($data)
    {
        return $this->saveSubAction($data);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        $this->completeSubSection();

        return $this->save($data);
    }

    /**
     * Complete sub section
     */
    protected function completeSubSection()
    {
        $sectionCompletion = $this->getSectionCompletion();

        $sectionName = $this->getSectionName();

        $key = 'section' . $sectionName . $this->getSubSectionName() . 'Status';

        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);

        $sectionCompletion[$key] = $completeKey;

        $sectionConfig = $this->getSectionConfig();

        $complete = true;

        foreach ($sectionConfig['subSections'] as $subSectionName => $details) {
            if (!isset($sectionCompletion['section' . $sectionName . $subSectionName . 'Status'])
                || $sectionCompletion['section' . $sectionName . $subSectionName . 'Status'] != $completeKey) {
                $complete = false;
                break;
            }
        }

        if ($complete) {

            $sectionCompletion['section' . $sectionName . 'Status'] = $completeKey;
        }

        $this->setSectionCompletion($sectionCompletion);

        $this->makeRestCall(
            $this->getJourneyConfig()['completionService'],
            'PUT',
            $this->getSectionCompletion()
        );
    }

    /**
     * Build the navigation view
     *
     * @return ViewModel
     */
    protected function getNavigationView()
    {
        $sections = $this->getAccessibleSections();

        $view = $this->getViewModel(
            array(
                'sections' => $sections
            )
        );

        $view->setTemplate('self-serve/journey/' . strtolower($this->getJourneyName()) . '/navigation');

        return $view;
    }

    /**
     * Get a list of accessible sections
     *
     * @return array
     */
    protected function getAccessibleSections()
    {
        $sections = $this->getSections();

        $sectionCompletion = $this->getSectionCompletion();

        $accessibleSections = array();

        $journeyName = $this->getJourneyName();

        $statusMap = $this->getJourneyConfig()['completionStatusMap'];

        foreach ($sections as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $sectionCompletion['section' . $name . 'Status'] = (int)$sectionCompletion['section' . $name . 'Status'];

            $status = $statusMap[$sectionCompletion['section' . $name . 'Status']];

            if ($name == $this->getSectionName()) {
                $status = 'current';
            }

            $accessibleSections[$name] = array(
                'status' => $status,
                'title' => $this->getSectionLabel($journeyName, $name),
                'route' => $this->getSectionRoute($journeyName, $name)
            );
        }

        return $accessibleSections;
    }

    /**
     * Check if a section is accessible
     *
     * @param array|string $details
     * @param string $section
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionAccessible($details, $section = null, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $section, $subSection);
        }

        if (isset($details['restriction'])) {

            $accessKeys = $this->getAccessKeys();

            $intersection = array_intersect($accessKeys, $details['restriction']);

            return !empty($intersection);
        }

        return true;
    }

    /**
     * Check if a section is enabled
     *
     * @param array|string $details
     * @param string $section
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionEnabled($details, $section = null, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $section, $subSection);
        }

        $sectionCompletion = $this->getSectionCompletion();

        $enabled = true;

        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);

        if (isset($details['required'])) {

            foreach ($details['required'] as $requiredSection) {
                $requiredSection = str_replace('/', '', $requiredSection);

                if (!isset($sectionCompletion['section' . $requiredSection . 'Status'])
                    || $sectionCompletion['section' . $requiredSection . 'Status'] != $completeKey) {

                    $enabled = false;
                }
            }
        }

        return $enabled;
    }

    /**
     * Get a list of access keys to match the restrictions
     *
     * This method should be extended
     *
     * @param boolean $force
     * @return array
     */
    protected function getAccessKeys($force = false)
    {
        return array(null);
    }

    /**
     * Get the sections
     *
     * @return array
     */
    protected function getSections()
    {
        return $this->getJourneyConfig()['sections'];
    }

    /**
     * Get an array of sub sections
     */
    protected function getSubSectionsForLayout()
    {
        $sectionConfig = $this->getSectionConfig();

        $subSections = array();

        $journey = $this->getJourneyName();
        $section = $this->getSectionName();

        foreach ($sectionConfig['subSections'] as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $enabled = $this->isSectionEnabled($details);

            $class = '';
            $link = true;

            if ($name == $this->getSubSectionName()) {
                $class = 'current';
            }

            if ($name == $this->getSubSectionName() && !$this->isSubAction()) {
                $link = false;
            }

            if (!$enabled) {
                $class = 'disabled';
                $link = false;
            }

            $subAction = false;

            $routeParams = array(
                $this->getJourneyConfig()['identifier'] => $this->getIdentifier()
            );

            if ($name == $this->getSubSectionName() && $this->isSubAction()) {
                $subAction = $this->getSectionReference();
            }

            $subSections[$name] = array(
                'label' => $this->getSectionLabel($journey, $section, $name),
                'class' => $class,
                'link' => $link,
                'route' => $this->getSectionRoute($journey, $section, $name),
                'routeParams' => $routeParams,
                'subAction' => $subAction
            );
        }

        return $subSections;
    }

    /**
     * Get the section config
     *
     * @return array
     */
    protected function getSectionConfig()
    {
        return $this->getJourneyConfig()['sections'][$this->getSectionName()];
    }

    /**
     * Format the section route
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function getSectionRoute($journey, $section, $subSection = null)
    {
        return $journey . '/' . $section . (!empty($subSection) ? '/' . $subSection : '');
    }

    /**
     * Format the section label
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function getSectionLabel($journey, $section, $subSection = null)
    {
        return strtolower(
            $this->camelToDash($journey . '.' . $section . (!empty($subSection) ? '.' . $subSection : ''))
        );
    }

    /**
     * Setter for section completion
     *
     * @param array $sectionCompletion
     */
    protected function setSectionCompletion($sectionCompletion)
    {
        $this->sectionCompletion = $sectionCompletion;
    }

    /**
     * Get the section completion
     *
     * @return array
     */
    protected function getSectionCompletion()
    {
        if (empty($this->sectionCompletion)) {
            $id = $this->getIdentifier();

            $foreignKey = $this->getJourneyConfig()['completionStatusJourneyIdColumn'];

            $completionStatus = $this->makeRestCall(
                $this->getJourneyConfig()['completionService'],
                'GET',
                array($foreignKey => $id)
            );

            $this->sectionCompletion = ($completionStatus['Count'] > 0 ? $completionStatus['Results'][0] : array());

            $this->sectionCompletion[$foreignKey] = $id;
        }

        return $this->sectionCompletion;
    }

    /**
     * Get the journey identifier
     *
     * @return int
     */
    protected function getIdentifier()
    {
        if (empty($this->identifier)) {
            $this->identifier = $this->params()->fromRoute($this->getJourneyConfig()['identifier']);
        }

        return $this->identifier;
    }

    /**
     * Check if the current sub section has a table
     *
     * @return boolean
     */
    protected function hasTable()
    {
        $tableName = $this->getTableName();

        $found = false;

        foreach ($this->getServiceLocator()->get('Config')['tables']['config'] as $location) {

            if (file_exists($location . $tableName . '.table.php' )) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * Get table name
     *
     * @return string
     */
    protected function getTableName()
    {
        if (empty($this->tableName)) {

            $this->tableName = $this->getFormName();
        }

        return $this->tableName;
    }

    /**
     * Check if the current sub section has a form
     *
     * @return boolean
     */
    protected function hasForm()
    {
        $formName = $this->getFormName();

        return file_exists($this->getServiceLocator()->get('Config')['local_forms_path'] . $formName . '.form.php');
    }

    /**
     * Get form name
     *
     * @return string
     */
    protected function getFormName()
    {
        if (empty($this->formName)) {

            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();

            $suffix = '';

            if ($this->isSubAction()) {

                $suffix = '-sub-action';
            }

            $this->formName = $this->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
        }

        return $this->formName;
    }

    /**
     * Get the section reference
     *
     * @return string
     */
    protected function getSectionReference()
    {
        if (empty($this->sectionReference)) {

            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();

            $suffix = '';

            if ($this->isSubAction()) {

                $suffix = '-' . $this->getAction();
            }

            $this->sectionReference = $this->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
        }

        return $this->sectionReference;
    }

    /**
     * Check if the current sub section has a view
     *
     * @return boolean
     */
    protected function hasView()
    {
        $viewName = $this->getViewName();

        return file_exists($this->getServiceLocator()->get('Config')['view_manager']['template_path_stack'][0] . '/' . $viewName . '.phtml');
    }

    /**
     * Get form name
     *
     * @return string
     */
    protected function getViewName()
    {
        if (empty($this->viewName)) {

            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();

            $this->viewName = $this->camelToDash($journey . '/' . $section . '/' . $subSection);
        }

        return $this->viewName;
    }

    /**
     * Convert camel case to dash
     *
     * @param string $string
     * @return string
     */
    private function camelToDash($string)
    {
        $converter = new \Zend\Filter\Word\CamelCaseToDash();
        return strtolower($converter->filter($string));
    }

    /**
     * Convert camel case to underscore
     *
     * @param string $string
     * @return string
     */
    private function camelToUnderscode($string)
    {
        $converter = new \Zend\Filter\Word\CamelCaseToUnderscore();
        return strtolower($converter->filter($string));
    }

    /**
     * Get the step number
     * @return type
     */
    protected function getStepNumber()
    {
        if (empty($this->stepNumber)) {
            $steps = $this->getSteps();

            $this->stepNumber = array_search(
                array(
                    $this->getJourneyName(),
                    $this->getSectionName(),
                    $this->getSubSectionName()
                ),
                $steps
            );
        }

        return $this->stepNumber;
    }

    /**
     * Redirect to the previous step
     */
    protected function goToPreviousStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $nextKey = $key - 1;

        while (isset($steps[$nextKey])) {

            if ($this->isSectionAccessible($steps[$nextKey][0], $steps[$nextKey][1], $steps[$nextKey][2])) {
                return $this->goToSection(
                    $this->getSectionRoute(
                        $steps[$nextKey][0],
                        $steps[$nextKey][1],
                        $steps[$nextKey][2]
                    )
                );
            }

            $nextKey--;
        }

        throw new \Exception('Can\'t find previous step');
    }

    /**
     * Redirect to the next step
     */
    protected function goToNextStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $nextKey = $key + 1;

        $this->getAccessKeys(true);

        while (isset($steps[$nextKey])) {

            if ($this->isSectionAccessible($steps[$nextKey][0], $steps[$nextKey][1], $steps[$nextKey][2])) {
                return $this->goToSection(
                    $this->getSectionRoute(
                        $steps[$nextKey][0],
                        $steps[$nextKey][1],
                        $steps[$nextKey][2]
                    )
                );
            }

            $nextKey++;
        }

        return $this->journeyFinished();
    }

    /**
     * Journey finished
     */
    protected function journeyFinished()
    {
        print '<pre>';
        print_r($this->getAccessKeys());
        print_r($this->getSteps());
        print '</pre>';
        die('Finished');
    }

    /**
     * Go to the first subSection
     *
     * @return Response
     */
    protected function goToFirstSubSection()
    {
        $name = $this->getJourneyName();
        $section = $this->getSectionName();
        $config = $this->getJourneyConfig();

        $route = $name . '/' . $section;

        if (isset($config['sections'][$section]['subSections'])) {
            $route .= '/' . array_keys($config['sections'][$section]['subSections'])[0];
        }

        return $this->goToSection($route);
    }

    /**
     * Go to the first section
     *
     * @return Response
     */
    protected function goToFirstSection()
    {
        $name = $this->getJourneyName();
        $config = $this->getJourneyConfig();

        $section = array_keys($config['sections'])[0];

        $route = $name . '/' . $section;

        if (isset($config['sections'][$section]['subSections'])) {
            $route .= '/' . array_keys($config['sections'][$section]['subSections'])[0];
        }

        return $this->goToSection($route);
    }

    /**
     * Redirect to a section
     *
     * @param string $name
     * @param array $params
     * @return Response
     */
    protected function goToSection($name, $params = array(), $reuse = true)
    {
        return $this->redirect()->toRoute($name, $params, array(), $reuse);
    }

    /**
     * Get the namespace parts
     *
     * @return array
     */
    private function getNamespaceParts()
    {
        $controller = get_called_class();

        return explode('\\', $controller);
    }

    /**
     * Getter for the current sub section name
     *
     * @return string
     */
    protected function getSubSectionName()
    {
        if (empty($this->subSectionName)) {

            $this->subSectionName = str_replace('Controller', '', $this->getNamespaceParts()[4]);

            if (!isset($this->getSectionConfig()['subSections'][$this->subSectionName])) {
                $this->subSectionName = null;
            }
        }

        return $this->subSectionName;
    }

    /**
     * Getter for the current section name
     *
     * @return string
     */
    protected function getSectionName()
    {
        if (empty($this->sectionName)) {
            $this->sectionName = $this->getNamespaceParts()[3];
        }

        return $this->sectionName;
    }

    /**
     * Getter for journey name
     *
     * @return string
     */
    protected function getJourneyName()
    {
        if (empty($this->journeyName)) {
            $this->journeyName = $this->getNamespaceParts()[2];
        }

        return $this->journeyName;
    }

    /**
     * Getter for the current journey config
     *
     * @return array
     */
    protected function getJourneyConfig()
    {
        if (empty($this->journeyConfig)) {
            $this->journeyConfig = $this->getServiceLocator()->get('Config')['journeys'][$this->getJourneyName()];
        }

        return $this->journeyConfig;
    }

    /**
     * Get stepts
     *
     * @return array
     */
    protected function getSteps()
    {
        if (empty($this->steps)) {
            $this->steps = array();

            $config = $this->getJourneyConfig();

            $journey = $this->getJourneyName();

            foreach ($config['sections'] as $section => $details) {

                if (isset($details['subSections'])) {

                    foreach ($details['subSections'] as $subSection => $subSectionDetails) {
                        $this->steps[] = array($journey, $section, $subSection);
                    }
                } else {
                    $this->steps[] = array($journey, $section, null);
                }
            }
        }

        return $this->steps;
    }

    /**
     * Get a journey, section, or sub section config
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return array
     */
    protected function getConfig($journey, $section = null, $subSection = null)
    {
        $config = $this->getJourneyConfig();

        if (!is_null($subSection)) {

            return isset($config['sections'][$section]['subSections'][$subSection])
                ? $config['sections'][$section]['subSections'][$subSection]
                : array();
        }

        if (!is_null($section)) {
            return isset($config['sections'][$section]) ? $config['sections'][$section] : array();
        }

        return $config;
    }
}
