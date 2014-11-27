<?php

/**
 * Licence Processing Helper
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Helper;

/**
 * Licence Processing Helper
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingHelper
{
    /**
     * Holds the section config
     *
     * @var array
     */
    protected $sections = array(
        'publications' => array(

        ),
        'notes' => array(

        ),
        'tasks' => array(

        ),
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
     * @param int $licenceId
     * @param string $activeSection
     * @return array
     */
    public function getNavigation($licenceId, $activeSection = null)
    {
        $sections = $this->getSections();

        $navigation = array();

        foreach (array_keys($sections) as $section) {
            $navigation[] = array(
                'label' => 'internal-licence-processing-' . $section . '-label',
                'title' => 'internal-licence-processing-' . $section . '-title',
                'route' => 'licence/processing/' . $section,
                'use_route_match' => true,
                'params' => array(
                    'licence' => $licenceId
                ),
                'active' => $section == $activeSection
            );
        }

        return $navigation;
    }
}
