<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;

/**
 * Class TmQualifications
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class TmQualifications extends AbstractSection
{
    /**
     * Generate TmQualifications Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $data = [];

        if (!empty($case->getTransportManager())) {
            $tmQualifications = $case->getTransportManager()->getQualifications();

            /** @var TmQualification $entity */
            foreach ($tmQualifications as $entity) {
                $thisRow = [];
                $thisRow['id'] = $entity->getId();
                $thisRow['version'] = $entity->getVersion();
                $thisRow['qualificationType'] = $entity->getQualificationType()->getDescription();
                $thisRow['serialNo'] = $entity->getSerialNo();
                $thisRow['issuedDate'] = $this->formatDate($entity->getIssuedDate());
                $thisRow['country'] = $entity->getCountryCode()->getCountryDesc();

                $data[] = $thisRow;
            }
        }

        return [
            'data' => [
                'tables' => [
                    'tm-qualifications' => $data,
                ]
            ]
        ];
    }
}
