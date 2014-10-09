<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

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
     * @param object $refDataService
     */
    public function setRefDataService($refDataService)
    {
        $this->refDataService = $refDataService;
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
}
