<?php

namespace Olcs\Controller\Traits;

use Zend\Filter\Word\DashToCamelCase;

/**
 * Trait for building submission section data
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
trait SubmissionSectionTrait
{

    /**
     * Create a sction from the submission config
     * @param type $config
     * @return type
     */
    public function createSubmissionSection($sectionId, $config = array())
    {
        $routeParams = $this->getParams(array('case'));
        $section['data'] = array();
        $bundle = isset($config['bundle']) ? $config['bundle'] : array();
        if (isset($config['service'])) {
            $this->sectionData = $this->makeRestCall(
                $config['service'],
                'GET',
                array('id' => $routeParams['case']),
                $bundle
            );
        }

        $filter = $this->getFilter();
        $method = 'get' . ucfirst($filter->filter($sectionId)) . 'SectionData';
        if (method_exists($this, $method)) {
            $section = call_user_func(array($this, $method), $this->sectionData);
        }

        return $section;
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
    private function getCaseOutlineSectionData(array $data = array())
    {
        $case = $this->getCase();

        return array(
            'outline' => $case['description']
        );
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
