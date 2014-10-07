<?php

namespace Olcs\Controller\Traits;

use Zend\Filter\Word\DashToCamelCase;

/**
 * Trait for building submission section data
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
trait SubmissionSectionTrait
{

    protected $allSectionData = array();

    /**
     * Create a section from the submission config
     *
     * @param string sectionId
     * @param array $sectionConfig for the section being generated
     * @return array $sectionData
     */
    public function createSubmissionSection($sectionId, $sectionConfig = array())
    {
        $routeParams = $this->getParams(array('case'));
        $section['data'] = array();
        echo '<p>Processing ' . $sectionId . '</p>';

        if (empty($sectionConfig)) {
            echo '<p>No config set</p>';
            return [];
        }
        $this->allSectionData[$sectionId] = $this->loadCaseSectionData($routeParams['case'], $sectionId,
            $sectionConfig);

        $section = $this->filterSectionData($sectionId);

        return $section;
    }

    /**
     * Loads the section data from either data already extracted or a new REST call
     *
     * @param $caseId
     * @param $sectionConfig
     * @return array
     */
    public function loadCaseSectionData($caseId, $sectionId, $sectionConfig)
    {
        // first check we haven't already extracted the data
        if (isset($this->allSectionData[$sectionId])) {
            echo '<p>Already got this data. Returning data from array.</p>';
            return $this->allSectionData[$sectionId];
        }

        if (isset($sectionConfig['bundle'])) {
            echo '<p>Bundle and service set for ' . $sectionId . '</p>';
            if (is_string($sectionConfig['bundle'])) {
                echo '<p>Bundle set to ' . $sectionConfig['bundle'] . '</p>';

                return $this->loadCaseSectionData(
                    $caseId,
                    $sectionConfig['bundle'],
                    $this->submissionConfig['sections'][$sectionConfig['bundle']]
                );

            } elseif (isset($sectionConfig['service']) && is_array($sectionConfig['bundle'])) {
                $rawData =  $this->makeRestCall(
                    $sectionConfig['service'],
                    'GET',
                    array('id' => $caseId),
                    $sectionConfig['bundle']
                );
                $this->allSectionData[$sectionId] = $rawData;
                echo '<p><b>Raw data:</b></p>';
                var_dump($rawData);
                return $rawData;
            }
        }

        return [];
    }

    /**
     * Method to get the filtered section data via callback method
     *
     * @param string $sectionId
     * @return array $sectionData
     */
    public function filterSectionData($sectionId)
    {
        echo '<p>Filtering section data</p>';
        $filteredSectionData = [];
        $filter = $this->getFilter();
        $method = 'get' . ucfirst($filter->filter($sectionId)) . 'SectionData';
        if (method_exists($this, $method)) {
            echo '<p>Making callback -> ' . $method . '</p>';
            $filteredSectionData = call_user_func(array($this, $method), $this->allSectionData[$sectionId]);
        }
        return $filteredSectionData;
    }

    private function getFilter()
    {
        return new DashToCamelCase();
    }

    /**
     * section case-summary
     */
    private function getCaseSummarySectionData(array $data = array())
    {
        $vehiclesInPossession = $this->calculateVehiclesInPossession($data['licence']);
        $filteredData = array(
            'id' => $data['id'],
            'organisationName' => $data['licence']['organisation']['name'],
            'isMlh' => $data['licence']['organisation']['isMlh'],
            'organisationType' => $data['licence']['organisation']['type']['description'],
            'businessType' => $data['licence']['organisation']['sicCode']['description'],
            'caseType' => isset($data['caseType']['id']) ? $data['caseType']['id'] : null,
            'ecmsNo' => $data['ecmsNo'],
            'licNo' => $data['licence']['licNo'],
            'licenceStartDate' => $data['licence']['inForceDate'],
            'licenceType' => $data['licence']['licenceType']['description'],
            'goodsOrPsv' => $data['licence']['goodsOrPsv']['description'],
            'serviceStandardDate' =>
                isset($data['application']['targetCompletionDate']) ?
                    $data['application']['targetCompletionDate'] : null,
            'licenceStatus' => $data['licence']['status']['description'],
            'totAuthorisedVehicles' => $data['licence']['totAuthVehicles'],
            'totAuthorisedTrailers' => $data['licence']['totAuthTrailers'],
            'vehiclesInPossession' => $vehiclesInPossession,
            'trailersInPossession' => $data['licence']['totAuthTrailers']
        );

        return $filteredData;
    }

    /**
     * section case-outline
     */
    private function getCaseOutlineSectionData($data = array())
    {
        return array(
            'outline' => $data['description']
        );
    }

    private function getConvictionFpnOffenceHistory($data = array()) {
        return $data;
    }

    /**
     * Calculates the vehicles in possession.
     *
     * @param array $data
     * @return int
     */
    private function calculateVehiclesInPossession($licenceData)
    {
        $vehiclesInPossession = 0;
        if (isset($licenceData['licenceVehicles']) && is_array($licenceData['licenceVehicles'])) {
            foreach ($licenceData['licenceVehicles'] as $vehicle) {
                if (!empty($vehicle['specifiedDate']) && empty($vehicle['deletedDate'])) {
                    $vehiclesInPossession++;
                }
            }
        }
        return $vehiclesInPossession;
    }
}
