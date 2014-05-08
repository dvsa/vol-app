<?php

namespace Olcs\Util;

/**
 * Trait for building submission section data
 * @author Mike Cooper
 */
trait SubmissionSectionDataTrait
{
    
    public $requiredDataKeys = array();
    
    public $dataToReturnArray = array();

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
