<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;

/**
 * SubmissionGeneratorAwareInterface
 */
interface SubmissionGeneratorAwareInterface
{
    public function setSubmissionGenerator(SubmissionGenerator $service);

    /**
     * @return SubmissionGenerator
     */
    public function getSubmissionGenerator();

    public function setSubmissionConfig(array $submissionConfig);

    /**
     * @return array submission configuration
     */
    public function getSubmissionConfig();
}
