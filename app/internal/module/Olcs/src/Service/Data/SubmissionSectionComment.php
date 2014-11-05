<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionSectionComment
 * @package Olcs\Service
 */
class SubmissionSectionComment extends AbstractData
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'SubmissionSectionComment';

    /**
     * Submission service
     *
     * @var object
     */
    private $submissionService;

    /**
     * Submission section Configuration file
     *
     * @var array
     */
    private $submissionConfig;

    /**
     * Create SubmissionSectionComment service with injected ref data service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SubmissionSectionComment
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);

        $submissionConfig = $serviceLocator->get('config')['submission_config'];
        $this->setSubmissionConfig($submissionConfig);

        $submissionService = $serviceLocator->get('Olcs\Service\Data\Submission');
        $this->setSubmissionService($submissionService);

        return $this;
    }


    /**
     * @return array
     */
    public function getBundle()
    {
        $bundle =  array(
            'properties' => 'ALL',
        );

        return $bundle;
    }

    public function generateComments($caseId, $data)
    {
        $commentData = [];
        if (is_array($data['submissionSections']['sections'])) {
            $submissionConfig = $this->getSubmissionConfig();

            foreach ($data['submissionSections']['sections'] as $sectionId) {

                $sectionConfig = isset($submissionConfig['sections'][$sectionId]) ?
                    $submissionConfig['sections'][$sectionId] : [];

                // if section type is text, generate sectionData for comment
                if (in_array('text', $sectionConfig['section_type'])) {
                    $sectionData =
                        $this->getSubmissionService()->createSubmissionSection($caseId, $sectionId, $sectionConfig);
                    array_push(
                        $commentData,
                        [
                            'id' => '',
                            'submissionSection' => $sectionId,
                            'comment' => $this->getDefaultComment($sectionId, $sectionConfig, $sectionData),
                        ]
                    );
                }
            }
        }
        return $commentData;
    }

    public function updateComments($caseId, $data)
    {
        $commentDataToAdd = [];
        $commentDataToRemove = [];
        $commentDataToKeep = [];
        $submissionConfig = $this->getSubmissionConfig();
        $submission = $this->getSubmissionService()->fetchSubmissionData($data['id']);
        $existingComments = $submission['submissionSectionComments'];

        // first remove any existing comments no longer required
        foreach ($existingComments as $comment) {
            if (!in_array($comment['submissionSection']['id'], $submissionConfig['mandatory-sections'])) {
                if (!in_array($comment['submissionSection']['id'], $data['submissionSections']['sections'])) {
                    // remove comment
                    $commentDataToRemove[] = $comment['id'];
                } else {
                    $commentDataToKeep[] = $comment['submissionSection']['id'];
                }
            }
        }

        if (is_array($data['submissionSections']['sections'])) {
            foreach ($data['submissionSections']['sections'] as $sectionId) {
                if (!in_array($sectionId, $submissionConfig['mandatory-sections'])) {

                    $sectionConfig = isset($submissionConfig['sections'][$sectionId]) ?
                        $submissionConfig['sections'][$sectionId] : [];

                    // if section type is text, generate sectionData for comment
                    if (in_array('text', $sectionConfig['section_type']) &&
                        !in_array($sectionId, $commentDataToKeep)
                    ) {
                        $sectionData =
                            $this->getSubmissionService()->createSubmissionSection($caseId, $sectionId, $sectionConfig);

                        array_push(
                            $commentDataToAdd,
                            [
                                'id' => '',
                                'submissionSection' => $sectionId,
                                'comment' => $this->getDefaultComment($sectionId, $sectionConfig, $sectionData),
                            ]
                        );
                    }
                }
            }
        }
        return ['add' => $commentDataToAdd, 'remove' => $commentDataToRemove];
    }

    private function getDefaultComment($sectionId, $sectionConfig, $sectionData)
    {
        $dataField = $sectionConfig['data_field'];

        if (empty($dataField) || !isset($sectionData[$dataField])) {
            $defaultComment = 'Placeholder for ' . $sectionId;
        } else {
            $defaultComment = $sectionData[$dataField];
        }
        return $defaultComment;
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
     * @param array $submissionService
     */
    public function setSubmissionService($submissionService)
    {
        $this->submissionService = $submissionService;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubmissionService()
    {
        return $this->submissionService;
    }

    /**
     * @param array $submissionConfig
     */
    public function setSubmissionConfig($submissionConfig)
    {
        $this->submissionConfig = $submissionConfig;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubmissionConfig()
    {
        return $this->submissionConfig;
    }
}
