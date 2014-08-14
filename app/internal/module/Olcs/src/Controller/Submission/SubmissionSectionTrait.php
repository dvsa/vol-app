<?php

namespace Olcs\Controller\Submission;

use Zend\Filter\Word\DashToCamelCase;

/**
 * Trait for building submission section data
 * @author Mike Cooper
 */
trait SubmissionSectionTrait
{

    /**
     * Return json encoded submission based on submission_config
     * @param type $routeParams
     * @return type
     */
    public function createSubmission($routeParams)
    {
        $licenceData = $this->makeRestCall('Licence', 'GET', array('id' => $routeParams['licence']));
        $submission = array();
        foreach ($this->submissionConfig['sections'] as $sectionName => $config) {
            if ($this->submissionExclude($config, $licenceData)) {
                $submission[$sectionName] = $this->createSubmissionSection($sectionName, $config);
            }
        }
        $jsonSubmission = json_encode($submission);
        return $jsonSubmission;
    }

    /**
     * builds a submission and excludes sections based on rules in
     * the submission config
     * @param type $section
     * @param type $config
     * @param type $licenceData
     * @return boolean
     */
    public function submissionExclude($config, $licenceData)
    {
        if (!isset($config['exclude'])) {
            return true;
        }
        if (in_array(strtolower($licenceData[$config['exclude']['column']]), $config['exclude']['values'])) {
            return true;
        }
        return false;
    }

    /**
     * Create a sction from the submission config
     * @param type $config
     * @return type
     */
    public function createSubmissionSection($sectionName, $config = array())
    {
        $routeParams = $this->getParams(array('case'));
        $section['data'] = array();
        $section['notes'] = array();
        $bundle = isset($config['bundle']) ? $config['bundle'] : array();
        if (isset($config['dataPath'])) {
            $this->sectionData = $this->makeRestCall(
                $config['dataPath'],
                'GET',
                array('id' => $routeParams['case']),
                $bundle
            );
        }
        $filter = new DashToCamelCase();
        $method = $filter->filter($sectionName);
        if (method_exists($this, $method)) {
            $section['data'] = $this->getFilteredSectionData($method, $this->sectionData);
        }

        return $section;
    }

    /**
     * Gets filtered result data for each submission section
     */
    public function getFilteredSectionData($method, $sectionData)
    {
        $data = call_user_func(array($this, $method), $sectionData);
        return $data;
    }

    /**
     * section case-summary-info
     */
    public function caseSummaryInfo(array $data = array())
    {
        return array(
            'id' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'name' => $data['licence']['organisation']['name'],
            'licenceType' => $data['licence']['licenceType'],
            'ecms_no' => $data['ecmsNo'],
            'description' => $data['description'],
            'type' => $data['licence']['organisation']['type'],
            'sicCode' => $data['licence']['organisation']['sicCode'],
            'isMlh' => $data['licence']['organisation']['isMlh'],
            'startDate' => $data['licence']['startDate'],
            'authorisedVehicles' => $data['licence']['authorisedVehicles'],
            'authorisedTrailers' => $data['licence']['authorisedTrailers'],
        );
    }

    /**
     * section conviction-history
     */
    public function convictionHistory(array $data = array())
    {
        $dataToReturnArray = array();
        $config = $this->getServiceLocator()->get('Config');

        $staticDefType = $config['static-list-data']['defendant_types'];

        foreach ($data['convictions'] as $conviction) {
            if ($conviction['category']['id'] != 168) {
                $thisConviction['description'] = $conviction['category']['description'];
            } else {
                $thisConviction['description'] = $conviction['categoryText'];
            }

            if ($conviction['operatorName']) {
                $thisConviction['name'] = $conviction['operatorName'];
            } else {
                $thisConviction['name'] = $conviction['personFirstname'] . ' ' . $conviction['personLastname'];
            }

            if (isset($staticDefType[$conviction['defType']])) {
                $thisConviction['name'] .= ' / ' . $staticDefType[$conviction['defType']];
            }

            $thisConviction['dateOfOffence'] = $conviction['dateOfOffence'];
            $thisConviction['convictionDate'] = $conviction['convictionDate'];

            $thisConviction['court'] = $conviction['court'];
            $thisConviction['penalty'] = $conviction['penalty'];
            $thisConviction['si'] = $conviction['si'];
            $thisConviction['decToTc'] = $conviction['decToTc'];
            $thisConviction['dealtWith'] = $conviction['dealtWith'];
            $dataToReturnArray[] = $thisConviction;
        }

        return $dataToReturnArray;
    }

    /**
     * section persons
     */
    public function persons(array $data = array())
    {
        $dataToReturnArray = array();

        foreach ($data['licence']['organisation']['organisationOwners'] as $organisationOwner) {

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
    public function transportManagers(array $data = array())
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
}
