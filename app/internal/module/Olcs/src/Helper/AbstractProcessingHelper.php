<?php

/**
 * Abstract Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Helper;

/**
 * Application Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractProcessingHelper
{
    /**
     * Holds the section config
     *
     * @var array
     */
    protected $sections = [
        'publications' => [],
        'inspection-request' => [],
        'notes' => [],
        'tasks' => []
    ];

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
     * @param int $id
     * @param string $activeSection
     * @return array
     */
    abstract public function getNavigation($id, $activeSection = null);
}
