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
    // Marker styles are ultimately used by the view helper as CSS classes.
    // If not specified, default is 'warning'.
    const MARKER_STYLE_DANGER  = 'danger';
    const MARKER_STYLE_WARNING = 'warning';
    const MARKER_STYLE_INFO    = 'info';
    const MARKER_STYLE_SUCCESS = 'success';

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
     * BusReg
     *
     * @var array
     */
    private $busReg = array();

    /**
     * TransportManager
     *
     * @var array
     */
    private $transportManager = array();

    /**
     * LicenceTransportManagers
     *
     * @var array
     */
    private $licenceTransportManagers = array();

    /**
     * ApplicationTransportManagers
     *
     * @var array
     */
    private $applicationTransportManagers = array();

    /**
     * Licence Status Rules
     *
     * @var array
     */
    private $licenceStatusRule = array();

    /**
     * @var array
     */
    private $continuationDetails = null;

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
        $this->setProperties($data);

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

    /**
     * Set the class propeties from the array
     *
     * @param array $data
     */
    protected function setProperties($data)
    {
        if (isset($data['case'])) {
            $this->setCase($data['case']);
        }

        if (isset($data['licence'])) {
            $this->setLicence($data['licence']);
        }

        if (isset($data['busReg'])) {
            $this->setBusReg($data['busReg']);
        }

        if (isset($data['licenceStatusRule'])) {
            $this->setLicenceStatusRule($data['licenceStatusRule']);
        }

        if (isset($data['continuationDetails'])) {
            $this->setContinuationDetails($data['continuationDetails']);
        }

        if (isset($data['transportManager'])) {
            $this->setTransportManager($data['transportManager']);
        }

        if (isset($data['licenceTransportManagers'])) {
            $this->setLicenceTransportManagers($data['licenceTransportManagers']);
        }

        if (isset($data['applicationTransportManagers'])) {
            $this->setApplicationTransportManagers($data['applicationTransportManagers']);
        }
    }

    protected function resetMarkers()
    {
        $this->markers                       = array();
        $this->case                          = array();
        $this->licence                       = array();
        $this->busReg                        = array();
        $this->transportManager              = array();
        $this->licenceTransportManagers      = array();
        $this->applicationTransportManagers  = array();
        $this->licenceStatusRule             = array();
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

    /**
     * @param array $busReg
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;
    }

    /**
     * @return array
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * @param array $transportManager
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;
    }

    /**
     * @return array
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * @param array $licenceTransportManagers
     */
    public function setLicenceTransportManagers($licenceTransportManagers)
    {
        $this->licenceTransportManagers = $licenceTransportManagers;
    }

    /**
     * @return array
     */
    public function getLicenceTransportManagers()
    {
        return $this->licenceTransportManagers;
    }

    /**
     * @param array $applicationTransportManagers
     */
    public function setApplicationTransportManagers($applicationTransportManagers)
    {
        $this->applicationTransportManagers = $applicationTransportManagers;
    }

    /**
     * @return array
     */
    public function getApplicationTransportManagers()
    {
        return $this->applicationTransportManagers;
    }

    /**
     * @param array $licenceStatusRule
     */
    public function setLicenceStatusRule($licenceStatusRule)
    {
        $this->licenceStatusRule = $licenceStatusRule;
    }

    /**
     * @return array
     */
    public function getLicenceStatusRule()
    {
        return $this->licenceStatusRule;
    }

    /**
     * @param array $continuationDetails
     */
    public function setContinuationDetails($continuationDetails)
    {
        $this->continuationDetails = $continuationDetails;
    }

    /**
     * @return array
     */
    public function getContinuationDetails()
    {
        return $this->continuationDetails;
    }
}
