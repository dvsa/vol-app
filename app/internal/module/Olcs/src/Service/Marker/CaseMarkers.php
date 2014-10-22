<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CaseMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class CaseMarkers extends AbstractData
{

    /**
     * Case
     *
     * @var array
     */
    private $case = array();

    private $markers = array();

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
     * Generate marker types based on array of types and data
     *
     * @param array $markerTypes
     * @param array $data
     * @return array
     */
    public function generateMarkerTypes($markerTypes, $data)
    {
        if (isset($data['case'])) $this->setCase($data['case']);

        if (is_array($markerTypes)) {
           foreach ($markerTypes as $type) {
               if (empty($this->getTypeMarkers($type)) && !empty($this->getCase())) {
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
        return $this->getMarkers();
    }

    /**
     * Gets the data required to generate the stay marker. Extracted from case.
     *
     * @return array
     */
    private function getStayMarkerData()
    {
        $case = $this->getCase();
        return [
            'stayData' => $case['stays'],
            'appealData' => $case['appeals'],
        ];
    }

    /**
     * Gets the data required to generate the appeal marker. Extracted from case.
     *
     * @return array
     */
    private function getAppealData()
    {
        $case = $this->getCase();
        return [
            'stayData' => $case['stays'],
            'appealData' => $case['appeals'],
        ];
    }

    /**
     * Generate the stay markers
     *
     * @param array $data
     * @return array
     */
    private function generateStayMarkers($data)
    {
        return [
            0 => [
                'content' => 'Stay1 granted pending appeal'
            ]
        ];
    }
}
