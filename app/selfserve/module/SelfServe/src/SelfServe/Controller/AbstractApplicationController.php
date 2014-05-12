<?php

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller;

use Zend\EventManager\EventManagerInterface;

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends AbstractJourneyController
{
    /**
     * Holds the applicationId
     */
    protected $applicationId;

    /**
     * Sub sections
     *
     * @var array
     */
    protected $subSections = array();


    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $controller = $this;
        $events->attach(
            'dispatch',
            function ($e) use ($controller) {

                $applicationId = $controller->getApplicationId();
                $currentSection = $controller->getCurrentSection();

                if (empty($applicationId) || empty ($currentSection)) {
                    return null;
                }

                $applicationCompletion = $controller->makeRestCall(
                    'Application',
                    'GET',
                    ['id' => $controller->getApplicationId()],
                    ['children' => ['completion']]
                )['completion'];

                //update last visited section
                $data = [
                    'version' => $applicationCompletion['version'],
                    'lastSection' => $controller->getCurrentSection(),
                    'id' => $applicationCompletion['id'],
                ];
                $this->makeRestCall('ApplicationCompletion', 'PUT', $data);
            },
            100
        ); // execute before executing action logic
    }

    /**
     * Render the layout with sub sections
     *
     * @param object $view
     * @param string $current
     * @param string $journey
     */
    public function renderLayoutWithSubSections($view, $current = '', $journey = '')
    {
        $subSections = $this->getSubSections();

        foreach ($subSections as $key => &$details) {

            $details['active'] = false;
            if ($key == $current) {
                $details['active'] = true;
            }
        }

        $applicationId = $this->getApplicationId();

        // collect completion status
        $completionStatus = $this->makeRestCall(
            'ApplicationCompletion',
            'GET',
            array('application_id' => $applicationId)
        );

        $layout = $this->getViewModel(
            array(
                'journey' => $journey,
                'subSections' => $subSections,
                'completionStatus' => ($completionStatus['Count'] > 0 ? $completionStatus['Results'][0] : array()),
                'applicationId' => $applicationId
            )
        );

        $layout->setTemplate('self-serve/layout/layout');

        $layout->addChild($view, 'main');

        return $layout;
    }

    /**
     * Getter for subSections
     *
     * @return array
     */
    public function getSubSections()
    {
        return $this->subSections;
    }

    /**
     * Setter for subSections
     *
     * @param array $subSections
     */
    protected function setSubSections($subSections = array())
    {
        $this->subSections = $subSections;
    }

    /**
     * Return the applicationId
     *
     * @return int
     */
    public function getApplicationId()
    {
        if (empty($this->applicationId)) {
            $this->applicationId = $this->params()->fromRoute('applicationId');
        }

        return $this->applicationId;
    }

    /**
     * Get licence entity based on route id value
     *
     * @return array|object
     */
    protected function getLicenceEntity($applicationId = false)
    {
        if ( ! $applicationId ) {
            $applicationId = (int) $this->getApplicationId('applicationId');
        }

        $bundle = array(
            'children' => array(
                'licence'
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);
        return $application['licence'];
    }
}
