<?php

/**
 * Application Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Helper;

/**
 * Application Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingHelper
{
    /**
     * Holds the section config
     *
     * @var array
     */
    protected $sections = array(
        'notes' => array(),
        'tasks' => array()
    );

    /**
     * Gets sections
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Sets sections
     *
     * @param array $sections
     * @return $this
     */
    public function setSections($sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * Gets navigation
     *
     * @param int $applicationId
     * @param string $activeSection
     * @return array
     */
    public function getNavigation($applicationId, $activeSection = null)
    {
        $sections = $this->getSections();

        $navigation = array();

        foreach (array_keys($sections) as $section) {
            $navigation[] = array(
                'label' => 'internal-application-processing-' . $section . '-label',
                'title' => 'internal-application-processing-' . $section . '-title',
                'route' => 'lva-application/processing/' . $section,
                'use_route_match' => true,
                'params' => array(
                    'application' => $applicationId
                ),
                'active' => $section == $activeSection
            );
        }

        return $navigation;
    }
}
