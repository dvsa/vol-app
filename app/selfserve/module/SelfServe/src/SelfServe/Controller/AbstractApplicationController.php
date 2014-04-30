<?php

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller;

use Common\Controller\FormJourneyActionController;

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends FormJourneyActionController
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

    /**
     * Render the layout with sub sections

     * @param object $view
     * @param string $current
     */
    protected function renderLayoutWithSubSections($view, $current = '')
    {
        $subSections = $this->getSubSections();

        foreach ($subSections as $key => &$details) {

            $details['active'] = false;
            if ($key == $current) {
                $details['active'] = true;
            }
        }

        $layout = $this->getViewModel(
            array(
                'subSections' => $subSections
            )
        );

        $layout->setTemplate('self-serve/layout/withSubSections');

        $layout->addChild($view, 'main');

        return $layout;
    }

    /**
     * Getter for subSections
     *
     * @return array
     */
    protected function getSubSections()
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
    protected function getApplicationId()
    {
        if (empty($this->applicationId)) {
            $this->applicationId = $this->params()->fromRoute('applicationId');
        }

        return $this->applicationId;
    }
}
