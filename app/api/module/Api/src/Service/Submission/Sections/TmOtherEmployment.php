<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;

/**
 * Class TmOtherEmployment
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class TmOtherEmployment extends AbstractSection
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
        $data = [];

        if (!empty($case->getTransportManager()->getEmployments())) {
            $tmEmployments = $case->getTransportManager()->getEmployments();

            /** @var TmEmployment $entity */
            foreach ($tmEmployments as $entity) {
                $thisRow = [];
                $thisRow['id'] = $entity->getId();
                $thisRow['version'] = $entity->getVersion();
                $thisRow['position'] = $entity->getPosition();
                $thisRow['employerName'] = $entity->getEmployerName();
                $thisRow['address'] = $entity->getContactDetails()->getAddress()->toArray();
                $thisRow['hoursPerWeek'] = $entity->getHoursPerWeek();

                $data[] = $thisRow;
            }
        }

        return [
            'data' => [
                'tables' => [
                    'tm-other-employment' => $data
                ]
            ]
        ];
    }
}
