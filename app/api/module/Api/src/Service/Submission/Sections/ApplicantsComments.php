<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class ApplicantsComments
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class ApplicantsComments extends AbstractSection
{
    /**
     * Generate ApplicantsComments Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $defaultText = $this->getViewRenderer()->render('/sections/applicants-comments.phtml');

        return ['data' => ['text' => $defaultText]];
    }
}
