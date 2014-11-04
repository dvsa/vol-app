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
        $sectionData = [];
        if (is_array($data['submissionSections']['sections'])) {
            $submissionConfig = $this->getSubmissionConfig();

            foreach ($data['submissionSections']['sections'] as $index => $sectionId) {

                $sectionConfig = isset($submissionConfig['sections'][$sectionId]) ?
                    $submissionConfig['sections'][$sectionId] : [];

                // if section type is text, generate sectionData for comment
                if (in_array('text', $sectionConfig['section_type']) && !empty($sectionConfig['data_field'])) {
                    $sectionData = $this->getSubmissionService()->createSubmissionSection(
                        $caseId,
                        $sectionId,
                        $sectionConfig
                    );

                    $dataField = $sectionConfig['data_field'];

                    if (empty($dataField) || !isset($sectionData[$dataField])) {
                        $initialComment = 'Placeholder for ' . $sectionId;
                    } else {
                        $initialComment = $sectionData[$dataField];
                    }

                    $commentData = [
                        "data" => json_encode([
                            "submissionSection" => $sectionId,
                            "submission" => $data['id'],
                            "comment" => $initialComment,
                        ])
                    ];

                    $this->getRestClient()->post('', $commentData);
                }
            }
        }
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
