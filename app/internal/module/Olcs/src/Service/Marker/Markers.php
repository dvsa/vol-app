<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CaseMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
abstract class Markers extends AbstractData
{
    /**
     * Case
     *
     * @var array
     */
    private $case = array();

    /**
     * Licence
     *
     * @var array
     */
    private $licence = array();

    /**
     * Markers array indexed by type
     * @var array
     */
    private $markers = array();

    /**
     * Generate marker types based on array of types and data
     *
     * @param array $markerTypes
     * @param array $data
     * @return array
     */
    public function generateMarkerTypes($markerTypes, $data)
    {
        if (isset($data['case'])) {
            $this->setCase($data['case']);
        }

        if (isset($data['licence'])) {
            $this->setLicence($data['licence']);
        }

        if (is_array($markerTypes)) {
            foreach ($markerTypes as $type) {
                $typeMarkers = $this->getTypeMarkers($type);
                if (empty($typeMarkers)) {
                    $generateMethod = 'generate' . ucfirst($type) . 'Markers';
                    $dataMethod = 'get' . ucfirst($type) . 'MarkerData';

                    if (method_exists($this, $dataMethod) && method_exists($this, $generateMethod)) {
                        $data = $this->$dataMethod();
                        $markers = $this->$generateMethod($data);
                        $this->setTypeMarkers($type, $markers);
                    }
                }
            }
        }

        $markers = $this->getMarkers();

        $this->resetMarkers();

        return $markers;
    }

    private function resetMarkers()
    {
        unset($this->markers);
        unset($this->case);
        unset($this->licence);
    }

    /**
     * Set markers for type
     *
     * @param string $type
     * @param array $markers
     *
     * @return object
     */
    public function setTypeMarkers($type, $markers)
    {
        $this->markers[$type] = $markers;
        return $this;
    }

    /**
     * Get markers for type
     *
     * @param string $type
     * @return array
     */
    public function getTypeMarkers($type)
    {
        if (isset($this->markers[$type]) && is_array($this->markers[$type])) {
            return $this->markers[$type];
        };
        return array();
    }

    /**
     * Set markers
     *
     * @param array
     * @return object
     */
    public function setMarkers($markers)
    {
        $this->markers = $markers;
        return $this;
    }

    /**
     * Get markers
     *
     * @return array
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * Set case
     *
     * @param array $case
     *
     * @return object
     */
    public function setCase($case)
    {
        $this->case = $case;
        return $this;
    }

    /**
     * Get case
     *
     * @return array
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @param array $licence
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;
    }

    /**
     * @return array
     */
    public function getLicence()
    {
        return $this->licence;
    }
}
