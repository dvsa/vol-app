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
        if ($this->isButtonPressed('back')) {
            return $this->goToPreviousStep();
        }

        if (empty($view)) {
            $view = $this->getViewModel($params);
        }

        if ($this->hasForm()) {

            $formName = $this->getFormName();
            $data = array();

            $form = $this->generateFormWithData($formName, 'processForm', $data);

            if ($this->getStepNumber() == 0) {
                $form->get('form-actions')->remove('back');
            }

            if ($form instanceof Response) {
                return $form;
            }

            $view->setVariable('form', $form);

            if ($view->getTemplate() == null) {
                $view->setTemplate('self-serve/layout/form');
            }
        }

        if ($this->hasView()) {
            $view->setTemplate($this->getViewName());
        }

        $layout = $this->getViewModel(
            array(
                'subSections' => $this->getSubSectionsForLayout(),
                'journey' => $this->getJourneyName(),
                'sectionCompletion' => $this->getSectionCompletion(),
                'id' => $this->getIdentifier()
            )
        );

        $layout->setTemplate('self-serve/layout/journey');

        $layout->addChild($view, 'main');

        return $layout;
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
            $subSections[$name] = array(
                'label' => $this->getSectionLabel($journey, $section, $name),
                'route' => $this->getSectionRoute($journey, $section, $name),
                'active' => ($name == $this->getSubSectionName())
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
    protected function getSectionLabel($journey, $section, $subSection)
    {
        return strtolower($this->camelToDash($journey . '.' . $section . '.' . $subSection));
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

            $completionStatus = $this->makeRestCall(
                $this->getJourneyConfig()['completionService'],
                'GET',
                array($this->camelToUnderscode($this->getJourneyConfig()['identifier']) => $id)
            );

            $this->sectionCompletion = ($completionStatus['Count'] > 0 ? $completionStatus['Results'][0] : array());
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

            $this->formName = $this->camelToDash($journey . '_' . $section . '_' . $subSection);
        }

        return $this->formName;
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
                $this->getSectionRoute(
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

        if ($steps[$nextKey]) {
            $this->goToSection($steps[$nextKey]);
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
        if ($steps[$nextKey]) {
            $this->goToSection($steps[$nextKey]);
        } else {
            $this->journeyFinished();
        }
    }

    /**
     * Journey finished
     */
    protected function journeyFinished()
    {
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
    protected function goToSection($name, $params = array())
    {
        return $this->redirect()->toRoute($name, $params, array(), true);
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
                        $this->steps[] = $this->getSectionRoute($journey, $section, $subSection);
                    }
                } else {
                    $this->steps[] = $this->getSectionRoute($journey, $section);
                }
            }
        }

        return $this->steps;
    }
}
