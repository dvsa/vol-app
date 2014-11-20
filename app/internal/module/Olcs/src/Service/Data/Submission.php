<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\Filter\Word\DashToCamelCase;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Data\CloseableInterface;

/**
 * Class Submission
 * @package Olcs\Service
 */
class Submission extends AbstractData implements CloseableInterface
{
    use CloseableTrait;

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
    protected $loadedSectionData = [];

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
     * Fetch submission data
     *
     * @param integer|null $id
     * @param array|null $bundle
     * @return array
     */
    public function fetchData($id = null, $bundle = null)
    {
        $id = is_null($id) ? $this->getId() : $id;
        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    /**
     * Extracts sections from dataSnapshot, adds description from refData to returned array and comments
     * for each section
     *
     * @param array $submission
     *
     * @return array
     */
    public function extractSelectedSubmissionSectionsData($submission)
    {
        $submissionSectionRefData = $this->getRefDataService()->fetchListOptions('submission_section');
        $submissionConfig = $this->getSubmissionConfig();

        $selectedSectionsArray = json_decode($submission['dataSnapshot'], true);

        // add section description text from ref data
        foreach ($selectedSectionsArray as $sectionId => $selectedSectionData) {
            $selectedSectionsArray[$sectionId]['sectionId'] = $sectionId;
            $selectedSectionsArray[$sectionId]['description'] = $submissionSectionRefData[$sectionId];
            $selectedSectionsArray[$sectionId]['comments'] = $this->filterCommentsBySection(
                $sectionId,
                $submission['submissionSectionComments']
            );

            // if we only have a type of text, then unset any other data to prevent comments being repeated
            if ($submissionConfig['sections'][$sectionId]['section_type'] == ['text']) {
                $selectedSectionsArray[$sectionId]['data'] = [];
            }
        }

        return $selectedSectionsArray;
    }

    /**
     * Loops through all comments attached to a submission and returns only those
     * which match the section
     *
     * @param string $sectionId
     * @param array $comments
     * @return array
     */
    public function filterCommentsBySection($sectionId, $comments)
    {
        $sectionComments = [];
        foreach ($comments as $comment) {
            if ($sectionId == $comment['submissionSection']['id']) {
                $sectionComments[] = $comment;
            }
        }
        return $sectionComments;
    }

    /**
     * Returns list of submission sections from ref data table
     * @return array
     */
    public function getAllSectionsRefData()
    {
        if (empty($this->allSectionsRefData)) {
            $this->allSectionsRefData =
                $this->getRefDataService()->fetchListOptions(
                    'submission_section'
                );
        }
        return $this->allSectionsRefData;
    }

    /**
     * Sets ref data list of submission sections
     *
     * @param $allSectionsRefData
     * @return $this
     */
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
     * @param integer $caseId
     * @param string $sectionId
     * @param array $sectionConfig for the section being generated
     * @return array $sectionData
     */
    public function createSubmissionSection($caseId, $sectionId, $sectionConfig = array())
    {
        $section['data'] = array();

        if (empty($sectionConfig)) {
            return [];
        }

        $loadedData = $this->loadCaseSectionData(
            $caseId,
            $sectionId,
            $sectionConfig
        );
        $this->setLoadedSectionDataForSection($sectionId, $loadedData);

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
        if (isset($this->getLoadedSectionData()[$sectionId])) {
            return $this->getLoadedSectionData()[$sectionId];
        }

        $rawData = [];

        if (isset($sectionConfig['bundle'])) {
            if (is_string($sectionConfig['bundle'])) {
                $rawData = $this->loadCaseSectionData(
                    $caseId,
                    $sectionConfig['bundle'],
                    $this->getSubmissionConfig()['sections'][$sectionConfig['bundle']]
                );
            } elseif (isset($sectionConfig['service']) && is_array($sectionConfig['bundle'])) {
                $rawData = $this->getApiResolver()->getClient(
                    $sectionConfig['service']
                )->get(
                    '',
                    array('id' => $caseId,
                    'bundle' => json_encode($sectionConfig['bundle'])
                    )
                );
            }
        }
        return $rawData;
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
            $filteredSectionData = call_user_func(array($this, $method), $this->getLoadedSectionData()[$sectionId]);
        }
        return $filteredSectionData;
    }

    private function getFilter()
    {
        return new DashToCamelCase();
    }

    /**
     * section oppositions
     */
    protected function filterOppositionsData(array $data = array())
    {
        $dataToReturnArray = array();
        if (isset($data['application']['oppositions']) && is_array($data['application']['oppositions'])) {

            usort(
                $data['application']['oppositions'],
                function ($a, $b) {
                    return strnatcmp($b['oppositionType']['description'], $a['oppositionType']['description']);
                }
            );
            usort(
                $data['application']['oppositions'],
                function ($a, $b) {
                    return strtotime($b['raisedDate']) - strtotime($a['raisedDate']);
                }
            );

            foreach ($data['application']['oppositions'] as $opposition) {
                $thisOpposition = array();
                $thisOpposition['id'] = $opposition['id'];
                $thisOpposition['version'] = $opposition['version'];
                $thisOpposition['dateReceived'] = $opposition['raisedDate'];
                $thisOpposition['oppositionType'] = $opposition['oppositionType']['description'];
                $thisOpposition['contactName']['forename'] =
                    $opposition['opposer']['contactDetails']['person']['forename'];
                $thisOpposition['contactName']['familyName'] =
                    $opposition['opposer']['contactDetails']['person']['familyName'];

                foreach ($opposition['grounds'] as $ground) {
                    $thisOpposition['grounds'][] = $ground['grounds']['description'];
                }

                $thisOpposition['isValid'] = $opposition['isValid'];
                $thisOpposition['isCopied'] = $opposition['isCopied'];
                $thisOpposition['isInTime'] = $opposition['isInTime'];
                $thisOpposition['isPublicInquiry'] = $opposition['isPublicInquiry'];
                $thisOpposition['isWithdrawn'] = $opposition['isWithdrawn'];

                $dataToReturnArray[] = $thisOpposition;
            }
        }

        return $dataToReturnArray;
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
        if (isset($data['convictions'])) {
            usort(
                $data['convictions'],
                function ($a, $b) {
                    return strtotime($b['convictionDate']) - strtotime($a['convictionDate']);
                }
            );

            $dataToReturnArray = array();

            foreach ($data['convictions'] as $conviction) {
                $thisConviction = array();
                $thisConviction['id'] = $conviction['id'];
                $thisConviction['offenceDate'] = $conviction['offenceDate'];
                $thisConviction['convictionDate'] = $conviction['convictionDate'];
                $thisConviction['defendantType'] = $conviction['defendantType'];

                if ($conviction['defendantType']['id'] == 'def_t_op') {
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
        }
        return $dataToReturnArray;
    }

    /**
     * Filters data for person section
     * @param array $data
     * @return array $dataToReturnArray
     */
    protected function filterPersonsData(array $data = array())
    {
        $dataToReturnArray = array();

        if ($data['licence']['organisation']['organisationPersons']) {
            usort(
                $data['licence']['organisation']['organisationPersons'],
                function ($a, $b) {
                    return strnatcmp($a['person']['forename'], $b['person']['forename']);
                }
            );
            foreach ($data['licence']['organisation']['organisationPersons'] as $organisationOwner) {
                $thisOrganisationOwner['id'] = $organisationOwner['person']['id'];
                $thisOrganisationOwner['title'] = $organisationOwner['person']['title'];
                $thisOrganisationOwner['familyName'] = $organisationOwner['person']['familyName'];
                $thisOrganisationOwner['forename'] = $organisationOwner['person']['forename'];
                $thisOrganisationOwner['birthDate'] = $organisationOwner['person']['birthDate'];
                $dataToReturnArray[] = $thisOrganisationOwner;

            }
        }
        return $dataToReturnArray;
    }

    /**
     * @codeCoverageIgnore Method not used, yet. Here for future story reference only.
     * section transportManagers
     */
    protected function filterTransportManagersDataNotUsed(array $data = array())
    {
        $dataToReturnArray = array();

        foreach ($data['licence']['transportManagerLicences'] as $TmLicence) {
            $thisTmLicence = array();
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
     * @param array $licenceData
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
     * Returns the bundle required to get a submission
     * @return array
     */
    public function getBundle()
    {
        $bundle =  array(
            'properties' => 'ALL',
            'children' => array(
                'submissionType' => array(
                    'properties' => 'ALL',
                ),
                'case' => array(
                    'properties' => 'ALL',
                ),
                'submissionSectionComments' =>  array(
                    'properties' => 'ALL',
                    'children' => array(
                        'submissionSection' => array(
                            'properties' => array(
                                'id'
                            )
                        )
                    )
                ),
                'submissionActions' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'recipientUser' => array(
                            'properties' => 'ALL'
                        ),
                        'senderUser' => array(
                            'properties' => 'ALL'
                        ),
                        'submissionActionStatus' => array(
                            'properties' => 'ALL'
                        ),
                        'reasons' => array(
                            'properties' => 'ALL',
                        )
                    )
                )
            )
        );

        return $bundle;
    }

    /**
     * Generates the sectionData for each submission section via business rules:
     *  - If new section data, add as normal
     *  - If updating submission with existing sections, keep existing data where sections are editable
     *
     * @param $caseId
     * @param $data
     * @return array
     */
    public function generateSnapshotData($caseId, $data)
    {
        $sectionData = [];
        if (is_array($data['submissionSections']['sections'])) {
            $submissionConfig = $this->getSubmissionConfig();

            foreach ($data['submissionSections']['sections'] as $index => $sectionId) {

                $sectionConfig = isset($submissionConfig['sections'][$sectionId]) ?
                    $submissionConfig['sections'][$sectionId] : [];

                // if section type is list, generate sectionData for snapshot
                $sectionData[$sectionId] = [
                    'data' => $this->createSubmissionSection(
                        $caseId,
                        $sectionId,
                        $sectionConfig
                    )
                ];
            }
        }

        return $sectionData;
    }

    /**
     * section oppositions
     */
    protected function filterConditionsAndUndertakingsData(array $data = array())
    {
        $dataToReturnArray = array();
        if (isset($data['conditionUndertakings']) && is_array($data['conditionUndertakings'])) {

            usort(
                $data['conditionUndertakings'],
                function ($a, $b) {
                    return strtotime($b['createdOn']) - strtotime($a['createdOn']);
                }
            );

            foreach ($data['conditionUndertakings'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['caseId'] = $entity['case']['id'];
                $thisEntity['addedVia'] = $entity['addedVia'];
                $thisEntity['isFulfilled'] = $entity['isFulfilled'];
                $thisEntity['isDraft'] = $entity['isDraft'];
                $thisEntity['attachedTo'] = $entity['attachedTo'];

                if (empty($entity['operatingCentre'])) {
                    $thisEntity['OcAddress'] = [];
                } else {
                    $thisEntity['OcAddress'] = $entity['operatingCentre']['address'];
                }
                $tableName = $entity['conditionType']['id'] == 'cdt_und' ? 'undertakings' : 'conditions';
                $dataToReturnArray[$tableName][] = $thisEntity;
            }
        }
        return $dataToReturnArray;
    }

    /**
     * Can this entity be closed
     * @param $id
     * @return bool
     */
    public function canClose($id)
    {
        return !$this->isClosed($id);
    }

    /**
     * Is this entity closed
     * @param $id
     * @return bool
     */
    public function isClosed($id)
    {
        $submission = $this->fetchData($id);
        return (bool) isset($submission['closedDate']);
    }

    /**
     * Can this entity be reopened
     * @param $id
     * @return bool
     */
    public function canReopen($id)
    {
        return $this->isClosed($id);
    }


    /**
     * Set refData service
     *
     * @param object $refDataService
     * @return object $this
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
     * Set ApiResolver
     * @param array $apiResolver
     *
     * @return object $this
     */
    public function setApiResolver($apiResolver)
    {
        $this->apiResolver = $apiResolver;
        return $this;
    }

    /**
     * get api resolver
     *
     * @return array
     */
    public function getApiResolver()
    {
        return $this->apiResolver;
    }

    /**
     * Sets the submission config
     *
     * @param array $submissionConfig
     * @return object $this
     */
    public function setSubmissionConfig($submissionConfig)
    {
        $this->submissionConfig = $submissionConfig;
        return $this;
    }

    /**
     * Gets the submission config
     *
     * @return array
     */
    public function getSubmissionConfig()
    {
        return $this->submissionConfig;
    }

    /**
     * Sets loadedSectionData
     *
     * @param array $loadedSectionData
     * @return object $this
     */
    public function setLoadedSectionData($loadedSectionData)
    {
        $this->loadedSectionData = $loadedSectionData;
        return $this;
    }

    /**
     * Sets LoadedSectionDataForSection
     *
     * @param $sectionId
     * @param $data
     * @return $this
     */
    public function setLoadedSectionDataForSection($sectionId, $data)
    {
        $this->loadedSectionData[$sectionId] = $data;
        return $this;
    }

    /**
     * Gets the loadedSectionData
     * @return array
     */
    public function getLoadedSectionData()
    {
        return $this->loadedSectionData;
    }
}
