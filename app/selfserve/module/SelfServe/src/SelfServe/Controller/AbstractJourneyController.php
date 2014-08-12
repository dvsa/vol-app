<?php

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller;

use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Abstract Journey Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractJourneyController extends AbstractController
{

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction;

    /**
     * Holds the action data
     *
     * @var array
     */
    protected $actionData;

    /**
     * Render the navigation
     *
     * @var boolean
     */
    protected $renderNavigation = true;

    /**
     * Holds the form tables
     *
     * @var array
     */
    protected $formTables;

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = null;

    /**
     * Holds the action service name
     *
     * @var string
     */
    protected $actionService = null;

    /**
     * Holds any inline scripts for the current page
     *
     * @var array
     */
    protected $inlineScripts = [];

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
     * Holds the action name
     *
     * @var string
     */
    private $actionName;

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
     * Holds the view name
     *
     * @var string
     */
    protected $viewName;

    /**
     * Holds the action id
     *
     * @var int
     */
    protected $actionId;

    /**
     * Holds hasView
     *
     * @var boolean
     */
    protected $hasView = null;

    /**
     * Holds hasTable
     *
     * @var boolean
     */
    protected $hasTable = null;

    /**
     * Holds hasForm
     *
     * @var boolean
     */
    protected $hasForm = null;

    /**
     * Override the not found action
     */
    public function notFoundAction()
    {
        $view = $this->getViewModel();
        $view->setTemplate('self-serve/journey/not-found');

        return $this->render($view);
    }

    /**
     * Delete
     *
     * @return Response
     */
    protected function delete()
    {
        $actionService = $this->getActionService();
        $actionId = $this->getActionId();

        if (!empty($actionService) && !empty($actionId)) {

            $this->makeRestCall($actionService, 'DELETE', array('id' => $actionId));

            return $this->goBackToSection();
        }

        return $this->notFoundAction();
    }

    /**
     * Render navigation
     *
     * @param boolean $renderNavigation
     */
    protected function setRenderNavigation($renderNavigation)
    {
        $this->renderNavigation = $renderNavigation;
    }

    /**
     * Get render navigation
     *
     * @return boolean
     */
    protected function getRenderNavigation()
    {
        return $this->renderNavigation;
    }

    /**
     * Gets the action data map
     *
     * @return array
     */
    protected function getActionDataMap()
    {
        return $this->actionDataMap;
    }

    /**
     * Getter for action data bundle
     *
     * @return array
     */
    protected function getActionDataBundle()
    {
        return $this->actionDataBundle;
    }

    /**
     * Get the sub action service
     *
     * @return string
     */
    protected function getActionService()
    {
        return $this->actionService;
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
     * Getter for the current sub section name
     *
     * @return string
     */
    protected function getSubSectionName()
    {
        if (empty($this->subSectionName)) {

            if (isset($this->getNamespaceParts()[4])) {
                $this->subSectionName = str_replace('Controller', '', $this->getNamespaceParts()[4]);
            }
        }

        return $this->subSectionName;
    }

    /**
     * Getter for action name
     *
     * @return string
     */
    protected function getActionName()
    {
        if (empty($this->actionName)) {
            $this->actionName = $this->params()->fromRoute('action');
        }

        return $this->actionName;
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
     * Get the sections
     *
     * @return array
     */
    protected function getSections()
    {
        return $this->getJourneyConfig()['sections'];
    }

    /**
     * Get the section config
     *
     * @return array
     */
    protected function getSectionConfig()
    {
        return $this->getSections()[$this->getSectionName()];
    }

    /**
     * Get a journey, section, or sub section config
     *
     * @param string $section
     * @param string $subSection
     * @return array
     */
    protected function getConfig($section = null, $subSection = null)
    {
        $config = $this->getJourneyConfig();

        if (!is_null($subSection)) {

            return isset($config['sections'][$section]['subSections'][$subSection])
                ? $config['sections'][$section]['subSections'][$subSection]
                : array();
        }

        return isset($config['sections'][(string) $section]) ? $config['sections'][(string) $section] : array();
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

            if ($this->isAction()) {
                $suffix = '-' . $this->getActionName();
            }

            $this->sectionReference = $this->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
        }

        return $this->sectionReference;
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
     * Get form name
     *
     * @return string
     */
    protected function getFormName()
    {
        if (empty($this->formName)) {

            $this->formName = $this->formatFormName(
                $this->getJourneyName(), $this->getSectionName(), $this->getSubSectionName(), $this->isAction()
            );
        }

        return $this->formName;
    }

    /**
     * Format a form name for a section
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @param string $isAction
     * @return string
     */
    protected function formatFormName($journey, $section, $subSection, $isAction = false)
    {
        $suffix = '';

        if ($isAction) {
            $suffix = '-sub-action';
        }

        return $this->camelToDash($journey . '_' . $section . '_' . $subSection . $suffix);
    }

    /**
     * Get the journey identifier
     *
     * @return int
     */
    protected function getIdentifier()
    {
        if (empty($this->identifier)) {
            $this->identifier = $this->params()->fromRoute($this->getIdentifierName());
        }

        return $this->identifier;
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
                $this->getJourneyConfig()['completionService'], 'GET', array($foreignKey => $id)
            );

            $this->sectionCompletion = ($completionStatus['Count'] > 0 ? $completionStatus['Results'][0] : array());

            $this->sectionCompletion[$foreignKey] = $id;
        }

        return $this->sectionCompletion;
    }

    /**
     * Set the steps
     */
    protected function setSteps()
    {
        $this->steps = array();

        $config = $this->getJourneyConfig();

        $journey = $this->getJourneyName();

        foreach ($config['sections'] as $section => $details) {

            foreach (array_keys($details['subSections']) as $subSection) {
                $this->steps[] = array($journey, $section, $subSection);
            }
        }
    }

    /**
     * Get stepts
     *
     * @return array
     */
    protected function getSteps()
    {
        if (empty($this->steps)) {
            $this->setSteps();
        }

        return $this->steps;
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
                array($this->getJourneyName(), $this->getSectionName(), $this->getSubSectionName()), $steps
            );
        }

        return $this->stepNumber;
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
        unset($force);

        if (empty($this->accessKeys)) {
            $this->accessKeys = array(null);
        }

        return $this->accessKeys;
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

            $this->viewName = $this->camelToDash('self-serve/' . $journey . '/' . $section . '/' . $subSection);

            if ($this->isAction()) {
                $this->viewName .= '-' . $this->getActionName();
            }
        }

        return $this->viewName;
    }

    /**
     * Get the sub action id
     *
     * @return int
     */
    protected function getActionId()
    {
        if (empty($this->actionId)) {
            $this->actionId = $this->params()->fromRoute('id');
        }

        return $this->actionId;
    }

    /**
     * Get a list of accessible sections
     *
     * @return array
     */
    protected function getAccessibleSections()
    {
        $accessibleSections = array();

        $sections = $this->getSections();

        foreach ($sections as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $accessibleSections[$name] = $this->getAccessibleSection($name);
        }

        return $accessibleSections;
    }

    /**
     * Format the accessible section
     *
     * @param string $name
     * @return array
     */
    protected function getAccessibleSection($name)
    {
        $journeyName = $this->getJourneyName();
        $sectionName = $this->getSectionName();

        $status = $this->getSectionStatus($name);

        if ($name == $sectionName) {
            $status = 'current';
        }

        return array(
            'status' => $status,
            'enabled' => $this->isSectionEnabled($name),
            'title' => $this->getSectionLabel($journeyName, $name),
            'route' => $this->getSectionRoute($journeyName, $name)
        );
    }

    /**
     * Get section status
     *
     * @param string $section
     * @return string
     */
    protected function getSectionStatus($section)
    {
        $sectionCompletion = $this->getSectionCompletion();

        $statusMap = $this->getJourneyConfig()['completionStatusMap'];

        return $statusMap[(int) $sectionCompletion['section' . $section . 'Status']];
    }

    /**
     * Build the navigation view
     *
     * @return ViewModel
     */
    protected function getNavigationView()
    {
        $sections = $this->getAccessibleSections();

        $view = $this->getViewModel(array('sections' => $sections));

        $view->setTemplate('self-serve/journey/' . strtolower($this->getJourneyName()) . '/navigation');

        return $view;
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
     * Format the section route
     *
     * @param string $journey
     * @param string $section
     * @param string $subSection
     * @return string
     */
    protected function getSectionRoute($journey = null, $section = null, $subSection = null)
    {
        if (is_null($journey) && is_null($section) && is_null($subSection)) {
            $journey = $this->getJourneyName();
            $section = $this->getSectionName();
            $subSection = $this->getSubSectionName();
        }

        return $journey . '/' . $section . (!empty($subSection) ? '/' . $subSection : '');
    }

    /**
     * Get an array of sub sections
     */
    protected function getSubSectionsForLayout()
    {
        $sectionConfig = $this->getSectionConfig();

        $subSections = array();

        foreach ($sectionConfig['subSections'] as $name => $details) {

            if (!$this->isSectionAccessible($details)) {
                continue;
            }

            $subSections[$name] = $this->getSectionDetailsForLayout($name, $details);
        }

        return $subSections;
    }

    /**
     * Format the section details for the layout
     *
     * @param string $name
     * @param array $details
     * @return array
     */
    protected function getSectionDetailsForLayout($name, $details)
    {
        $journey = $this->getJourneyName();
        $section = $this->getSectionName();
        $subSection = $this->getSubSectionName();
        $isAction = $this->isAction();

        $sectionDetails = array(
            'label' => $this->getSectionLabel($journey, $section, $name),
            'class' => '',
            'link' => true,
            'route' => $this->getSectionRoute($journey, $section, $name),
            'routeParams' => array($this->getIdentifierName() => $this->getIdentifier()),
            'action' => false
        );

        if (!$this->isSectionEnabled($details)) {
            $sectionDetails['class'] = 'disabled';
            $sectionDetails['link'] = false;
        } else {

            if ($name == $subSection) {
                $sectionDetails['class'] = 'current';
            }

            if ($name == $subSection && !$isAction) {
                $sectionDetails['link'] = false;
            }
        }

        if ($name == $subSection && $isAction) {
            $sectionDetails['action'] = $this->getSectionReference();
        }

        return $sectionDetails;
    }

    /**
     * Check if the current sub section has a view
     *
     * @return boolean
     */
    protected function hasView()
    {
        if (is_null($this->hasView)) {

            $this->hasView = file_exists(
                $this->getServiceLocator()->get(
                    'Config'
                )['view_manager']['template_path_stack'][0] . '/' . $this->getViewName() . '.phtml'
            );
        }

        return $this->hasView;
    }

    /**
     * Check if the current sub section has a table
     *
     * @return boolean
     */
    protected function hasTable()
    {
        if (is_null($this->hasTable)) {

            $tableName = $this->getTableName();

            $this->hasTable = false;

            foreach ($this->getServiceLocator()->get('Config')['tables']['config'] as $location) {

                if (file_exists($location . $tableName . '.table.php')) {
                    $this->hasTable = true;
                    break;
                }
            }
        }

        return $this->hasTable;
    }

    /**
     * Check if the current sub section has a form
     *
     * @return boolean
     */
    protected function hasForm()
    {
        if (is_null($this->hasForm)) {
            $this->hasForm = $this->formExists($this->getFormName());
        }

        return $this->hasForm;
    }

    /**
     * Check if a section is accessible
     *
     * @param array|string $details (Section if string)
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionAccessible($details, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $subSection);
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
     * @param array|string $details (Section if string)
     * @param string $subSection
     * @return boolean
     */
    protected function isSectionEnabled($details, $subSection = null)
    {
        if (is_string($details)) {
            $details = $this->getConfig($details, $subSection);
        }

        $sectionCompletion = $this->getSectionCompletion();

        $enabled = true;

        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);

        if (isset($details['required'])) {

            foreach ($details['required'] as $requiredSection) {

                if (strstr($requiredSection, '/')) {
                    list($sectionName, $subSectionName) = explode('/', $requiredSection);
                } else {
                    $sectionName = $requiredSection;
                    $subSectionName = null;
                }

                $requiredSection = str_replace('/', '', $requiredSection);

                if ($this->isSectionAccessible($sectionName, $subSectionName)
                    && (!isset($sectionCompletion['section' . $requiredSection . 'Status'])
                    || $sectionCompletion['section' . $requiredSection . 'Status'] != $completeKey)) {

                    $enabled = false;
                }
            }
        }

        if ($enabled && isset($details['enabled'])) {

            if (is_callable(array($this, $details['enabled']))) {
                $enabled = $this->$details['enabled']();
            }
        }

        return $enabled;
    }

    /**
     * Check if we have a sub action
     *
     * @return boolean
     */
    protected function isAction()
    {
        if (is_null($this->isAction)) {
            $action = $this->getActionName();
            $this->isAction = ($action != 'index');
        }

        return $this->isAction;
    }

    /**
     * Alter table
     *
     * This method should be overridden
     *
     * @param object $table
     * @return object
     */
    protected function alterTable($table)
    {
        return $table;
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
     * Alter the action form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterActionForm($form)
    {
        return $form;
    }

    /**
     * Render the section
     *
     * @return Response
     */
    protected function renderSection($view = null, $params = array())
    {
        $redirect = $this->checkForRedirect();

        if ($redirect instanceof Response || $redirect instanceof ViewModel) {
            return $redirect;
        }

        $view = $this->setupView($view, $params);

        $this->maybeAddTable($view);

        $response = $this->maybeAddForm($view);

        if ($response instanceof Response || $response instanceof ViewModel) {
            return $response;
        }

        $this->maybeAddScripts($view);

        $view = $this->preRender($view);

        return $this->render($view);
    }

    /**
     * Pre render
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function preRender($view)
    {
        return $view;
    }

    protected function maybeAddScripts($view)
    {
        $scripts = $this->getInlineScripts();
        if (empty($scripts)) {
            return;
        }

        // this process defers to a service which takes care of checking
        // whether the script(s) exist
        $view->setVariable('scripts', $this->loadScripts($scripts));
    }

    protected function getInlineScripts()
    {
        return $this->inlineScripts;
    }

    /**
     * Potentiall add a table to the view
     *
     * @param ViewModel $view
     */
    protected function maybeAddTable($view)
    {
        if ($this->hasTable()) {

            $tableName = $this->getTableName();

            $data = $this->getTableData($this->getIdentifier());

            $settings = $this->getTableSettings();

            $table = $this->alterTable($this->getTable($tableName, $data, $settings));

            $view->setVariable('table', $table->render());
        }
    }

    /**
     * Potentially add a form
     *
     * @param ViewModel $view
     * @return Response
     */
    protected function maybeAddForm($view)
    {
        if ($this->hasForm()) {

            $data = array();

            if (!$this->getRequest()->isPost()) {
                $data = $this->getFormData();
            }

            if ($this->isAction() || empty($this->formTables)) {
                $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $data);
            } else {
                $tableConfigs = array();

                foreach ($this->formTables as $table => $config) {
                    $tableConfigs[$table] = array(
                        'config' => $config,
                        'data' => $this->getFormTableData($this->getIdentifier(), $table)
                    );
                }

                $form = $this->generateTableFormWithData(
                    $this->getFormName(),
                    array(
                        'success' => $this->getFormCallback(),
                        'crud_action' => $this->getFormCallback() . 'Crud'
                    ),
                    $data,
                    $tableConfigs
                );
            }

            $response = $this->getCaughtResponse();

            if ($response instanceof Response || $response instanceof ViewModel) {
                return $response;
            }

            $view->setVariable('form', $form);
        }
    }

    /**
     * Alter the form before validation
     *
     * @param Form $form
     * @return Form
     */
    protected function alterFormBeforeValidation($form)
    {
        // @todo Might want to take this out so we can go back to dashboard
        if ($this->getStepNumber() == 0) {
            $form->get('form-actions')->remove('back');
        }

        if ($this->isAction()) {
            $action = $this->getActionName();

            if (strstr($action, '-')) {
                list($prefix, $action) = explode('-', $action);
                unset($prefix);
            }

            if ($action == 'edit') {
                $form->get('form-actions')->remove('addAnother');
            }
        }

        $alterMethod = $this->getAlterFormMethod();

        return $this->$alterMethod($form);
    }

    /**
     * Determine the alter form method name
     *
     * @return string
     */
    protected function getAlterFormMethod()
    {
        if ($this->isAction()) {
            return 'alterActionForm';
        }

        return 'alterForm';
    }

    /**
     * Get the form data
     *
     * @return array
     */
    protected function getFormData()
    {
        if ($this->isAction()) {

            $action = $this->getActionName();

            if (strstr($action, '-')) {
                $splitted = explode('-', $action);
                $action = count($splitted) ? $splitted[count($splitted) - 1] : '';
            }

            if ($action === 'edit') {

                $data = $this->actionLoad($this->getActionId());
            } else {
                $data = array();
            }

            $processedData = $this->processActionLoad($data);
        } else {

            $data = $this->loadCurrent();

            $processedData = $this->processLoad($data);
        }

        return $processedData;
    }

    /**
     * Simple helper method to load the current application
     *
     * @return array
     */
    protected function loadCurrent()
    {
        return $this->load($this->getIdentifier());
    }

    /**
     * Get the form callback
     *
     * @return string
     */
    protected function getFormCallback()
    {
        $callback = 'processSave';

        if ($this->isAction()) {
            $callback = 'processActionSave';
        }

        return $callback;
    }

    /**
     * Setup the view for renderring
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function setupView($view = null, $params = array())
    {
        $journeyName = $this->getJourneyName();

        if (empty($view)) {
            $view = $this->getViewModel($params);
        }

        if ($this->isAction()) {
            $view->setVariable('title', $this->getSectionReference());
        }

        if ($this->hasView()) {
            $view->setTemplate($this->getViewName());
        } elseif ($view->getTemplate() == null) {
            $view->setTemplate('self-serve/journey/' . strtolower($journeyName) . '/main');
        }

        return $view;
    }

    /**
     * Check for redirect
     *
     * @return Response
     */
    protected function checkForRedirect()
    {
        $sectionName = $this->getSectionName();
        $subSectionName = $this->getSubSectionName();

        $crudAction = $this->checkForCrudAction();

        if ($crudAction instanceof Response || $crudAction instanceof ViewModel) {
            return $crudAction;
        }

        if (!$this->isSectionAccessible($sectionName, null)
            || !$this->isSectionAccessible($sectionName, $subSectionName)) {
            return $this->goToNextStep();
        }

        if (!$this->isSectionEnabled($sectionName, null)
            || !$this->isSectionEnabled($sectionName, $subSectionName)
            || $this->isButtonPressed('back')) {
            return $this->goToPreviousStep();
        }

        if ($this->isAction() && $this->isButtonPressed('cancel')) {
            return $this->goBackToSection();
        }
    }

    /**
     * Render the view
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function render($view)
    {
        $layout = $this->getViewModel(
            array(
                'subSections' => $this->getSubSectionsForLayout(),
                'isCollapsible' => $this->isCollapsible(),
                'id' => $this->getIdentifier()
            )
        );

        $layoutName = $this->getLayout();

        if (empty($layoutName)) {
            $layoutName = 'self-serve/journey/' . strtolower($this->getJourneyName()) . '/layout';
        }

        $layout->setTemplate($layoutName);

        $children = array();

        if ($this->getRenderNavigation()) {
            $navigation = $this->getNavigationView();
            $layout->addChild($navigation, 'navigation');
            $children[] = 'navigation';
        }

        $children[] = 'main';

        $layout->addChild($view, 'main');

        $layout->setVariable('children', $children);

        return $layout;
    }

    /**
     * Load sub section data
     *
     * @param int $id
     * @return array
     */
    protected function actionLoad($id)
    {
        if (empty($this->actionData)) {

            $this->actionData = $this->makeRestCall(
                $this->getActionService(),
                'GET',
                array('id' => $id),
                $this->getActionDataBundle()
            );
        }

        return $this->actionData;
    }

    /**
     * Process loading the sub section data
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        return $data;
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        if ($this->shouldCollapseSection()) {
            $this->completeSection();
        } else {
            $this->completeSubSection();
        }

        $response = parent::processSave($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        $this->setCaughtResponse($this->goToNextStep());
    }

    /**
     * Save sub action data
     *
     * @param array $data
     */
    protected function actionSave($data, $service = null)
    {
        $method = 'POST';

        if (isset($data['id']) && !empty($data['id'])) {
            $method = 'PUT';
        }

        if (is_null($service)) {
            $service = $this->getActionService();
        }

        return $this->makeRestCall($service, $method, $data);
    }

    /**
     * Save the sub action
     *
     * @param array $data
     * @return array
     */
    protected function processActionSave($data, $form)
    {
        unset($form);

        $data = $this->processDataMapForSave($data, $this->getActionDataMap());

        $response = $this->actionSave($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        $this->setCaughtResponse($this->postActionSave());
    }

    /**
     * Post action save (Decide where to go)
     *
     * @return Response
     */
    protected function postActionSave()
    {
        if ($this->isButtonPressed('addAnother')) {
            return $this->goBackToAddAnother();
        }

        return $this->goBackToSection();
    }

    /**
     * Complete the current sub section
     */
    protected function completeSubSection()
    {
        $this->completeSubSections([$this->getSubSectionName()]);
    }

    /**
     * Complete the current over-arching section and all its subsections
     */
    protected function completeSection()
    {
        $section = $this->getSectionConfig();
        $subSections = array_keys($section['subSections']);
        $this->completeSubSections($subSections);
    }

    /**
     * Complete an array of sub sections; this allows multiple steps
     * to be marked complete while only triggering a single API request
     *
     * @param array $subSections
     */
    protected function completeSubSections(array $subSections)
    {

        $sectionCompletion = $this->getSectionCompletion();
        $sectionName = $this->getSectionName();
        $completeKey = array_search('complete', $this->getJourneyConfig()['completionStatusMap']);
        $incompleteKey = array_search('incomplete', $this->getJourneyConfig()['completionStatusMap']);
        $sectionConfig = $this->getSectionConfig();

        foreach ($subSections as $subSection) {
            $key = 'section' . $sectionName . $subSection . 'Status';
            $sectionCompletion[$key] = $completeKey;

            $complete = true;

            foreach (array_keys($sectionConfig['subSections']) as $subSectionName) {
                $sectionStatusKey = 'section' . $sectionName . $subSectionName . 'Status';

                if ($this->isSectionAccessible($sectionName, $subSectionName)
                    && (!isset($sectionCompletion[$sectionStatusKey])
                    || $sectionCompletion[$sectionStatusKey] != $completeKey)) {
                    $complete = false;
                    break;
                }
            }

            $sectionCompletionKey = ($complete ? $completeKey : $incompleteKey);

            $sectionCompletion['section' . $sectionName . 'Status'] = $sectionCompletionKey;
        }

        $this->makeRestCall($this->getJourneyConfig()['completionService'], 'PUT', $sectionCompletion);

        $sectionCompletion['version'] ++;

        $this->setSectionCompletion($sectionCompletion);
    }

    /**
     * Journey finished
     */
    protected function journeyFinished()
    {
        return $this->goHome();
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
        return $this->redirectToRoute($name, $params, array(), $reuse);
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
     * Redirect to the next step
     */
    protected function goToNextStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $startSection = $steps[$key][1];

        $nextKey = $key + 1;

        $this->getAccessKeys(true);

        while (isset($steps[$nextKey])) {
            list($app, $section, $subSection) = $steps[$nextKey];

            $nextKey++;

            if ($this->shouldCollapseSection() && $section === $startSection) {
                continue;
            }

            if ($this->isSectionAccessible($section, $subSection)) {
                return $this->goToSection(
                    $this->getSectionRoute($app, $section, $subSection)
                );
            }
        }

        return $this->journeyFinished();
    }

    /**
     * Redirect to the previous step
     */
    protected function goToPreviousStep()
    {
        $steps = $this->getSteps();

        $key = $this->getStepNumber();

        $prevKey = $key - 1;

        while (isset($steps[$prevKey])) {
            list($app, $section, $subSection) = $steps[$prevKey];

            $prevKey--;

            $subSection = $this->shouldCollapseSection($section) ? null : $subSection;

            if ($this->isSectionAccessible($section, $subSection)) {

                return $this->goToSection(
                    $this->getSectionRoute($app, $section, $subSection)
                );
            }
        }

        return $this->goHome();
    }

    /**
     * Check whether this section is suitable for collapsing (i.e. merging
     * all sub sections into one top-level step) and whether the appropriate
     * preconditions which satisfy a collapse are set.
     *
     * @param string $section
     *
     * @return bool
     */
    protected function shouldCollapseSection($section = null)
    {
        return $this->isCollapsible($section) && $this->isJavaScriptSubmission();
    }

    /**
     * Does this section, or a given section, indicate that it could
     * be collapsible?
     *
     * @param string $section
     *
     * @return bool
     */
    protected function isCollapsible($section = null)
    {
        if ($section === null) {
            $section = $this->getSectionName();
        }

        $sectionConfig = $this->getConfig($section);

        return isset($sectionConfig['collapsible']) && $sectionConfig['collapsible'];
    }

    /**
     * Is this a POST, and if so was it from a JS-enabled browser?
     *
     * @return bool
     */
    protected function isJavaScriptSubmission()
    {
        $request = $this->getRequest();

        return $request->isPost() && $request->getPost('js-submit');
    }

    /**
     * Redirect to sub section
     *
     * @return Response
     */
    protected function goBackToSection()
    {
        $route = $this->getSectionRoute();

        return $this->goToSection($route, array($this->getIdentifierName() => $this->getIdentifier()), false);
    }

    /**
     * Get the identifier name
     *
     * @return string
     */
    protected function getIdentifierName()
    {
        return $this->getJourneyConfig()['identifier'];
    }

    /**
     * Go back to sub action
     *
     * @return Response
     */
    protected function goBackToAddAnother()
    {
        $route = $this->getSectionRoute();

        return $this->goToSection($route);
    }

    /**
     * Go back to the home route
     *
     * @return Response
     */
    protected function goHome()
    {
        return $this->redirectToRoute($this->getJourneyConfig()['homeRoute']);
    }
}
