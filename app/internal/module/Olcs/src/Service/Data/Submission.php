<?php

namespace Olcs\Service\Data;

use Common\Service\Data\RefData;

/**
 * Class Submission
 *
 * @package Olcs\Service
 */
class Submission
{
    /**
     * Ref data service
     * @var RefData
     */
    protected $refDataService;

    /**
     * Ref data for all sections
     * @var array
     */
    protected $allSectionsRefData = [];

    /**
     * Create service instance
     *
     *
     * @return Submission
     */
    public function __construct(
        RefData $refDataService
    ) {
        $this->refDataService = $refDataService;
    }

    /**
     * Extracts sections from dataSnapshot, adds description from refData to returned array and comments
     * for each section
     *
     * @param array $submission               Submission
     * @param array $submissionSectionRefData Submission section ref data
     * @param array $submissionConfig         Submission config
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
     * @param string $sectionId Section id
     * @param array  $comments  Comments
     *
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
     *
     * @return array
     */
    public function getAllSectionsRefData()
    {
        if (empty($this->allSectionsRefData)) {
            $this->allSectionsRefData =
                $this->refDataService->fetchListOptions(
                    'submission_section'
                );
        }

        return $this->allSectionsRefData;
    }

    /**
     * Sets ref data list of submission sections
     *
     * @param array $allSectionsRefData All sections ref data
     *
     * @return $this
     */
    public function setAllSectionsRefData($allSectionsRefData)
    {
        $this->allSectionsRefData = $allSectionsRefData;

        return $this;
    }
}
