<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class AnnualTestHistory
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class AnnualTestHistory extends AbstractSection
{
    /**
     * Generate AnnualTestHistory section of submission
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        return ['data' => ['text' => $case->getAnnualTestHistory()]];
    }
}
