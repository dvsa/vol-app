<?php

namespace Olcs\Controller\Submission;

use Zend\Filter\Word\DashToCamelCase;

/**
 * Trait for building submission section data
 * @author Mike Cooper
 */
trait SubmissionSectionTrait
{
    
    public $requiredDataKeys = array();
    
    public $dataToReturnArray = array();
    
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
            if ($this->submissionExclude($sectionName, $config, $licenceData)) {
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
    public function submissionExclude($section, $config, $licenceData)
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
            $sectionData = $this->makeRestCall($config['dataPath'], 'GET', array('id' => $routeParams['case']), $bundle);
            $filter = new DashToCamelCase();
            $method = $filter->filter($sectionName);
            $section['data'] = $this->getFilteredSectionData($method, $sectionData);
        }
        return $section;
    }
    
    public function getFilteredSectionData($method, $sectionData)
    {
        $data = call_user_func(array($this, $method), $sectionData);
        return $data;
    }

    public function caseSummaryInfo(array $data = array())
    {
        $this->dataToReturnArray = array();
        
        $this->dataToReturnArray = array(
            'caseNumber' =>  $data['caseNumber'],
            'licenceNumber' =>  $data['licence']['licenceNumber'],
            'name' =>  $data['licence']['organisation']['name'],
            'licenceType' =>  $data['licence']['licenceType'],
            'ecms' =>  $data['ecms'],
            'description' =>  $data['description'],
            'organisationType' =>  $data['licence']['organisation']['organisationType'],
            'sicCode' =>  $data['licence']['organisation']['sicCode'],
            'isMlh' =>  $data['licence']['organisation']['isMlh'],
            'startDate' =>  $data['licence']['startDate'],
            'authorisedVehicles' =>  $data['licence']['authorisedVehicles'],
            'authorisedTrailers' =>  $data['licence']['authorisedTrailers'],
            );
        return $this->dataToReturnArray;
    }
    
    public function getFilteredDataValues($value, $key)
    {
        if (in_array($key, $this->requiredDataKeys)) {
            $this->dataToReturnArray[$key] = $value;
        }
    }
}
