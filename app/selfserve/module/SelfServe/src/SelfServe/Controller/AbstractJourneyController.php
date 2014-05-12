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
        if (empty($view)) {
            $view = $this->getViewModel($params);
        }

        if ($this->hasForm()) {

            $formName = $this->getFormName();
            $data = array();

            $form = $this->generateFormWithData($formName, 'processForm', $data);

            if ($form instanceof Response) {
                return $form;
            }

            $view->setVariable('form', $form);
        }

        $completionStatus = $this->getSectionCompletion();
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

            $this->sectionCompletion = $this->makeRestCall(
                $this->getJourneyConfig()['completionService'],
                'GET',
                array('id' => $id)
            );
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
}
