<?php

namespace Olcs\Controller\Traits;

use Zend\Filter\Word\UnderscoreToCamelCase;

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

        $filter = new UnderscoreToCamelCase();
        $method = lcfirst($filter->filter($sectionId));
        if (method_exists($this, $method)) {
            $section = call_user_func(array($this, $method), $this->sectionData);
        }

        return $section;
    }

    /**
     * section case-summary-info
     */
    public function submissionSectionCasu(array $data = array())
    {
        $vehiclesInPossession = 0;
        if (isset($data['licence']['licenceVehicles'])) {
            foreach ($data['licence']['licenceVehicles'] as $vehicle) {
                if (isset($vehicle['specifiedDate']) && empty($vehicle['specifiedDate'])) {
                    $vehiclesInPossession++;
                }
            }
        }
        return array(
            'id' => $data['id'],
            'organisationName' => $data['licence']['organisation']['name'],
            'isMlh' => $data['licence']['organisation']['isMlh'],
            'organisationType' => $data['licence']['organisation']['type']['description'],
            'businessType' => $data['licence']['organisation']['sicCode']['description'],
            'ecmsNo' => $data['ecmsNo'],
            'licNo' => $data['licence']['licNo'],
            'licenceStartDate' => $data['licence']['inForceDate'],
            'licenceType' => $data['licence']['licenceType']['description'],
            'serviceStandardDate' => $data['licence']['createdOn'], // + 9 weeks?
            'licenceStatus' => $data['licence']['status']['description'],
            'totAuthorisedVehicles' => $data['licence']['totAuthVehicles'],
            'totAuthorisedTrailers' => $data['licence']['totAuthTrailers'],
            'vehiclesInPossession' => $vehiclesInPossession,
            'trailersInPossession' => $data['licence']['totAuthTrailers']
        );
    }
}
