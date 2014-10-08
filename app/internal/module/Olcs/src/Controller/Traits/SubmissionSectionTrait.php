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

        if (empty($sectionConfig)) {
            return [];
        }

        $this->allSectionData[$sectionId] = $this->loadCaseSectionData(
            $routeParams['case'],
            $sectionId,
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
            return $this->allSectionData[$sectionId];
        }

        if (isset($sectionConfig['bundle'])) {
            if (is_string($sectionConfig['bundle'])) {

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
        $filteredSectionData = [];
        $filter = $this->getFilter();
        $method = 'filter' . ucfirst($filter->filter($sectionId)) . 'Data';
        if (method_exists($this, $method)) {
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
    protected function filterCaseSummaryData(array $data = array())
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
    protected function filterCaseOutlineData($data = array())
    {
        return array(
            'outline' => $data['description']
        );
    }

    /**
     * Conviction FPN Offence History section
     *
     * @param array $data
     * @return array
     */
    protected function filterConvictionFpnOffenceHistoryData($data = array())
    {
        $dataToReturnArray = array();

        foreach ($data['convictions'] as $conviction) {

            //if (isset($staticDefType[$conviction['defType']])) {
            //    $thisConviction['name'] .= ' / ' . $staticDefType[$conviction['defType']];
            //}

            $thisConviction['offenceDate'] = $conviction['offenceDate'];
            $thisConviction['convictionDate'] = $conviction['convictionDate'];

            if ($conviction['operatorName']) {
                $thisConviction['name'] = $conviction['operatorName'];
            } else {
                $thisConviction['name'] = $conviction['personFirstname'] . ' ' . $conviction['personLastname'];
            }

            $thisConviction['categoryText'] = $conviction['categoryText'];
            $thisConviction['court'] = $conviction['court'];
            $thisConviction['penalty'] = $conviction['penalty'];
            $thisConviction['msi'] = $conviction['msi'];
            $thisConviction['isDeclared'] = !empty($conviction['isDeclared']) ?
                $conviction['isDeclared'] : 'N';
            $thisConviction['isDealtWith'] = !empty($conviction['isDealtWith']) ?
            $conviction['isDealtWith'] : 'N';
            $dataToReturnArray[] = $thisConviction;
        }

        return $dataToReturnArray;
    }

    /**
     * section persons
     */
    protected function filterPersonsDataNotUsed(array $data = array())
    {
        $dataToReturnArray = array();

        foreach ($data['licence']['organisation']['organisationPersons'] as $organisationOwner) {

            $thisOrganisationOwner['familyName'] = $organisationOwner['person']['familyName'];
            $thisOrganisationOwner['forename'] = $organisationOwner['person']['forename'];
            $thisOrganisationOwner['birthDate'] = $organisationOwner['person']['birthDate'];
            $dataToReturnArray[] = $thisOrganisationOwner;

        }

        return $dataToReturnArray;
    }

    /**
     * section transportManagers
     */
    protected function filterTransportManagersDataNotUsed(array $data = array())
    {
        $dataToReturnArray = array();

        foreach ($data['licence']['transportManagerLicences'] as $TmLicence) {

            $thisTmLicence['familyName'] = $TmLicence['transportManager']['contactDetails']['person']['familyName'];
            $thisTmLicence['forename'] = $TmLicence['transportManager']['contactDetails']['person']['forename'];
            $thisTmLicence['tmType'] = $TmLicence['transportManager']['tmType'];
            $thisTmLicence['qualifications'] = '';

            foreach ($TmLicence['transportManager']['qualifications'] as $qualification) {
                $thisTmLicence['qualifications'] .= $qualification['qualificationType'].' ';
            }

            $thisTmLicence['birthDate'] = $TmLicence['transportManager']['contactDetails']['person']['birthDate'];
            $dataToReturnArray[] = $thisTmLicence;
        }

        return $dataToReturnArray;
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
