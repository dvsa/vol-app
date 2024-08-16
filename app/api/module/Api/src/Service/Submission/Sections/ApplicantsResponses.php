<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class ApplicantsResponses
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class ApplicantsResponses extends AbstractSection
{
    /**
     * Generate ApplicantsResponses section of submission
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $defaultText = $this->getViewRenderer()->render('/sections/applicants-responses.phtml');

        return ['data' => ['text' => $defaultText]];
    }
}
