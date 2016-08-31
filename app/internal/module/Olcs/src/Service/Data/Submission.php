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
     * FilterManager service
     * @var object
     */
    protected $filterManager;

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

        $filterManager = $serviceLocator->get('FilterManager');
        $this->setFilterManager($filterManager);

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
    public function extractSelectedSubmissionSectionsData($submission, $submissionSectionRefData, $submissionConfig)
    {
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
        $section = array();

        if (empty($sectionConfig)) {
            return [];
        }

        $loadedData = $this->loadCaseSectionData(
            $caseId,
            $sectionId,
            $sectionConfig
        );
        $this->setLoadedSectionDataForSection($sectionId, $loadedData);

        if (isset($sectionConfig['filter']) && $sectionConfig['filter']) {
            $section = $this->filterSectionData($sectionId);
        }

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
            if (isset($sectionConfig['service']) && is_array($sectionConfig['bundle'])) {
                $identifier = isset($sectionConfig['identifier']) ? $sectionConfig['identifier'] : 'id';
                $results = $this->getApiResolver()->getClient(
                    $sectionConfig['service']
                )->get(
                    '',
                    array($identifier => $caseId,
                        'bundle' => json_encode($sectionConfig['bundle'])
                    )
                );

                if (isset($results['Results'])) {
                    $rawData = $results['Results'];
                } else {
                    $rawData = $results;
                }
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
        $filter = $this->getFilter();

        // load filter class
        $filteredSectionData = $this->getFilterManager()
            ->get('Olcs/Filter/SubmissionSection/' . ucfirst($filter->filter($sectionId)))
            ->filter($this->getLoadedSectionData()[$sectionId]);

        return $filteredSectionData;
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
     * Retrieves submission documents by category and subcategory
     * @param $id
     * @return mixed
     */
    public function getDocuments($id)
    {
        $documentBundle = [
            'children' => [
                'documents'
            ]
        ];

        $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($documentBundle)]);

        return $data['documents'];
    }

    /**
     * Returns the bundle required to get a submission
     * @return array
     */
    public function getBundle()
    {
        $bundle =  array(
            'children' => array(
                'recipientUser',
                'senderUser',
                'documents' => array(
                    'children' => array(
                        'category',
                        'subCategory'
                    )
                ),
                'submissionType' => array(),
                'case' => array(),
                'submissionSectionComments' =>  array(
                    'children' => array(
                        'submissionSection' => array()
                    )
                ),
                'submissionActions' => array(
                    'children' => array(
                        'actionTypes' => array(),
                        'reasons' => array()
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

    private function getFilter()
    {
        return new DashToCamelCase();
    }

    /**
     * @param object $filterManager
     */
    public function setFilterManager($filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @return object
     */
    public function getFilterManager()
    {
        return $this->filterManager;
    }
}
