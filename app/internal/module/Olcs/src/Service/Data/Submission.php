<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\Filter\Word\DashToCamelCase;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Submission
 * @package Olcs\Service
 */
class Submission extends AbstractData
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'Submission';

    /**
     * Ref data service
     * @var object
     */
    protected $refDataService;

    /**
     * Ref data for all sections
     * @var array
     */
    protected $allSectionsRefData = [];

    /**
     * All user selected section data
     *
     * @var array
     */
    protected $allSectionData = [];

    /**
     * ApiResolver attached to perform submission section REST calls on different entities
     *
     * @var array
     */
    private $apiResolver;

    /**
     * Submission section Configuration file
     *
     * @var array
     */
    private $submissionConfig;

    /**
     * Create Submission service with injected ref data service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Submission
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);
        $apiResolver = $serviceLocator->get('ServiceApiResolver');
        $this->setApiResolver($apiResolver);

        $refDataService = $serviceLocator->get('Common\Service\Data\RefData');
        $this->setRefDataService($refDataService);

        $submissionConfig = $serviceLocator->get('config')['submission_config'];
        $this->setSubmissionConfig($submissionConfig);

        return $this;
    }

    /**
     * @param integer|null $id
     * @param array|null $bundle
     * @return array
     */
    public function extractSelectedSubmissionSectionsData($submission)
    {
        $submissionSectionRefData = $this->getRefDataService()->fetchListOptions('submission_section');

        $selectedSectionsArray = json_decode($submission['text'], true);

        // add section description text from ref data
        foreach ($selectedSectionsArray as $index => $selectedSectionData) {
            $selectedSectionsArray[$index]['description'] =
                $submissionSectionRefData[$selectedSectionData['sectionId']];
        }

        return $selectedSectionsArray;
    }

    public function getAllSectionsRefData()
    {
        if (empty($this->allSectionsRefData))
        {
            $this->allSectionsRefData =
                $this->getRefDataService()->fetchListOptions(
                    'submission_section'
                );
        }
        return $this->allSectionsRefData;
    }

    public function setAllSectionsRefData($allSectionsRefData)
    {
        $this->allSectionsRefData = $allSectionsRefData;
        return $this;
    }

    /**
     * Extracts the title from ref_data based on a given submission type.
     *
     * @param string $submissionType
     * @return string
     */
    public function getSubmissionTypeTitle($submissionType)
    {
        $submissionTitles = $this->getRefDataService()->fetchListData('submission_type_title');

        if (is_array($submissionTitles)) {
            foreach ($submissionTitles as $title) {
                if ($title['id'] == str_replace('_o_', '_t_', $submissionType)) {
                    return $title['description'];
                }
            }
        }
        return '';
    }

    /**
     * Create a section from the submission config
     *
     * @param string sectionId
     * @param array $sectionConfig for the section being generated
     * @return array $sectionData
     */
    public function createSubmissionSection($caseId, $sectionId, $sectionConfig = array())
    {
        $section['data'] = array();

        if (empty($sectionConfig)) {
            return [];
        }

        $this->allSectionData[$sectionId] = $this->loadCaseSectionData(
            $caseId,
            $sectionId,
            $sectionConfig);

        $section = $this->filterSectionData($sectionId);

        return $section;
    }

    /**
     * Loads the section data from either data already extracted or a new REST call
     *
     * @param $caseId
     * @param $sectionId
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
                    $this->getSubmissionConfig()['sections'][$sectionConfig['bundle']]
                );

            } elseif (isset($sectionConfig['service']) && is_array($sectionConfig['bundle'])) {

                $rawData = $this->getApiResolver()->getClient($sectionConfig['service'])->get('',
                    array('id' => $caseId,
                    'bundle' => json_encode($sectionConfig['bundle'])));

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

    /**
     * @param object $refDataService
     */
    public function setRefDataService($refDataService)
    {
        $this->refDataService = $refDataService;
        return $this;
    }

    /**
     * @return object
     */
    public function getRefDataService()
    {
        return $this->refDataService;
    }

    /**
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $apiResolver
     */
    public function setApiResolver($apiResolver)
    {
        $this->apiResolver = $apiResolver;
        return $this;
    }

    /**
     * @return array
     */
    public function getApiResolver()
    {
        return $this->apiResolver;
    }

    /**
     * @param array $submissionConfig
     */
    public function setSubmissionConfig($submissionConfig)
    {
        $this->submissionConfig = $submissionConfig;
    }

    /**
     * @return array
     */
    public function getSubmissionConfig()
    {
        return $this->submissionConfig;
    }

}
