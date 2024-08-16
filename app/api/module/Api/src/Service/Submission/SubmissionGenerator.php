<?php

namespace Dvsa\Olcs\Api\Service\Submission;

use Dvsa\Olcs\Api\Service\Submission\Sections\AbstractSection;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;

/**
 * Class SubmissionGenerator - Responsible for generating the submission section data and storing the json encoded data
 * snapshot against the submission entity
 *
 * @package Dvsa\Olcs\Api\Service\Submission
 */
class SubmissionGenerator
{
    public const MAX_GENERATE_SUBMISSION_TIME = 90;

    /**
     * Construct the submission generator
     *
     * @param array $submissionConfig Submission Config
     * @param SectionGeneratorPluginManager $sectionGeneratorPluginManager Generators Plugin manager
     *
     * @return void
     */
    public function __construct(private array $submissionConfig, private readonly SectionGeneratorPluginManager $sectionGeneratorPluginManager)
    {
    }

    /**
     * Generates a submission after first determining sections to generate and sets it against the submission entity
     *
     * @param SubmissionEntity $submissionEntity Entity
     * @param array            $sections         Sections
     *
     * @return SubmissionEntity
     * @throws \Exception
     */
    public function generateSubmission(SubmissionEntity $submissionEntity, $sections, $repos = [])
    {
        $isTm = $submissionEntity->getCase()->isTm();

        $sectionTypeId = $submissionEntity->getSubmissionType()->getId();

        if (!isset($this->submissionConfig['section-types'][$sectionTypeId])) {
            throw new \Exception('Invalid submission type');
        }

        $requiredSections = $this->getRequiredSections($sectionTypeId, $sections, $isTm);

        $snapshotData = $submissionEntity->getDataSnapshot();
        $snapshot = json_decode($snapshotData ?? '[]', true);

        //  store and set new limit of execution time
        $timeLimit = ini_get('MAX_EXECUTION_TIME');
        set_time_limit(self::MAX_GENERATE_SUBMISSION_TIME);

        // foreach section
        foreach ($requiredSections as $sectionId) {
            if (isset($snapshot[$sectionId])) {
                $data = $snapshot[$sectionId];
            } else {
                $data = $this->generateSubmissionSectionData($submissionEntity, $sectionId, null, $repos);
            }

            $submissionEntity->setSectionData($sectionId, $data);
        }

        $submissionEntity->setSubmissionDataSnapshot();

        //  restore limit of execution time
        set_time_limit($timeLimit);

        return $submissionEntity;
    }

    /**
     * Generates the snapshot data for a given sectionId by calling it's relevent section handler object
     *
     * @param SubmissionEntity $submissionEntity Entity
     * @param int              $sectionId        Section Id
     * @param string           $subSection       Sub Section
     *
     * @return mixed
     */
    public function generateSubmissionSectionData(SubmissionEntity $submissionEntity, $sectionId, $subSection = null, $repos = [])
    {
        /** @var AbstractSection $section */
        $section = $this->sectionGeneratorPluginManager->get($sectionId);
        $section->setRepos($repos);

        $data = $section->generateSection($submissionEntity->getCase());

        if (!empty($subSection) && isset($data[$subSection])) {
            return $data[$subSection];
        }

        return $data;
    }

    /**
     * Uses default list for submission type, along with those posted in request and returns a list of sections
     * to generate
     *
     * @param int     $sectionTypeId  Section Type Id
     * @param array   $postedSections Posted Sections
     * @param boolean $isTm           Is Transport Manager
     *
     * @return array|mixed
     */
    private function getRequiredSections($sectionTypeId, $postedSections, $isTm)
    {
        $submissionTypeSections = $this->submissionConfig['section-types'][$sectionTypeId];

        $sections = array_unique(array_merge($submissionTypeSections, $postedSections));

        if ($isTm) {
            $sectionsToRemove = $this->submissionConfig['excluded-tm-sections'];
            foreach ($sectionsToRemove as $sectionToRemove) {
                $sections = $this->removeSection($sections, $sectionToRemove);
            }
        }

        return $sections;
    }

    /**
     * Remove a section (sectionToRemove) from array $sections
     *
     * @param array $sections        Section
     * @param array $sectionToRemove Remove sections
     *
     * @return array
     */
    private function removeSection($sections, $sectionToRemove)
    {
        if (($key = array_search($sectionToRemove, $sections, true)) !== false) {
            unset($sections[$key]);
        }
        return $sections;
    }
}
