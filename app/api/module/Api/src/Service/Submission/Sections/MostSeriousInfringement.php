<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SeriousInfringmentEntity;

/**
 * Class MostSeriousInfringement
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class MostSeriousInfringement extends AbstractSection
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
        $seriousInfringements = $case->getSeriousInfringements();
        $data = [];
        $data['id'] = '';
        $erruRequest = $case->getErruRequest();
        $data['notificationNumber'] = !empty($erruRequest) ? $erruRequest->getNotificationNumber() : '';
        $data['siCategory'] = '';
        $data['siCategoryType'] = '';
        $data['infringementDate'] = '';
        $data['checkDate'] = '';
        $data['isMemberState'] = true;

        if (isset($seriousInfringements[0]) && $seriousInfringements[0] instanceof SeriousInfringmentEntity) {
            /** @var SeriousInfringmentEntity $mostSeriousInfringement */
            $mostSeriousInfringement = $seriousInfringements[0];
            $data['id'] = $mostSeriousInfringement->getId();
            $data['siCategory'] = !empty($mostSeriousInfringement->getSiCategory()) ?
                $mostSeriousInfringement->getSiCategory()->getDescription() : '';
            $data['siCategoryType'] = !empty($mostSeriousInfringement->getSiCategoryType()) ?
                $mostSeriousInfringement->getSiCategoryType()->getDescription() : '';
            $data['infringementDate'] = $this->formatDate($mostSeriousInfringement->getInfringementDate());
            $data['checkDate'] =  $this->formatDate($mostSeriousInfringement->getCheckDate());
        }

        return ['data' => ['overview' => $data]];
    }
}
