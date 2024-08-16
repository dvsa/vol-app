<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class LeadTcArea
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class LeadTcArea extends AbstractSection
{
    /**
     * Generate LeadTcArea section of submission
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $licence = $case->getLicence();
        $organisation = !empty($licence) ? $licence->getOrganisation() : null;
        $leadTcArea = !empty($organisation) ? $organisation->getLeadTcArea() : null;
        $leadTcAreaName = !empty($leadTcArea) ? $leadTcArea->getName() : null;
        return ['data' => ['text' => $leadTcAreaName]];
    }
}
