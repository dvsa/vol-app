<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;

/**
 * Class TmPreviousHistory
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class TmPreviousHistory extends AbstractSection
{
    /**
     * Generate TmPreviousHistory Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $convictionPenaltyData = [];
        $revokedCurtailedSuspendedLicences = [];

        if (!empty($case->getTransportManager())) {
            $previousConvictions = $case->getTransportManager()->getPreviousConvictions();

            /** @var PreviousConviction $entity */
            foreach ($previousConvictions as $entity) {
                $thisRow = [];
                $thisRow['id'] = $entity->getId();
                $thisRow['version'] = $entity->getVersion();
                $thisRow['offence'] = $entity->getCategoryText();
                $thisRow['convictionDate'] = $this->formatDate($entity->getConvictionDate());
                $thisRow['courtFpn'] = $entity->getCourtFpn();
                $thisRow['penalty'] = $entity->getPenalty();

                $convictionPenaltyData[] = $thisRow;
            }

            $otherLicences = $case->getTransportManager()->getOtherLicences();

            /** @var OtherLicence $entity */
            foreach ($otherLicences as $entity) {
                $thisRow = [];
                $thisRow['id'] = $entity->getId();
                $thisRow['version'] = $entity->getVersion();
                $thisRow['licNo'] = $entity->getLicNo();
                $thisRow['holderName'] = $entity->getHolderName();

                $revokedCurtailedSuspendedLicences[] = $thisRow;
            }
        }

        return [
            'data' => [
                'tables' => [
                    'convictions-and-penalties' => $convictionPenaltyData,
                    'revoked-curtailed-suspended-licences' => $revokedCurtailedSuspendedLicences
                ]
            ]
        ];
    }
}
