<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Class LinkedLicences
 *
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class LinkedLicences extends AbstractSection
{
    /**
     * Generate LinkedLicences Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $data = [];
        /** @var Licence $licence */
        if (!empty($case->getLicence())) {
            foreach ($case->getLicence()->getOrganisation()->getLinkedLicences() as $licence) {
                $thisRow = [];
                if ($licence->getId() !== $case->getLicence()->getId()) {
                    $thisRow['id'] = $licence->getId();
                    $thisRow['version'] = $licence->getVersion();
                    $thisRow['licNo'] = $licence->getLicNo();
                    $thisRow['status'] = !empty($licence->getStatus()) ? $licence->getStatus()->getDescription() : null;
                    $thisRow['licenceType'] = !empty($licence->getLicenceType()) ?
                        $licence->getLicenceType()->getDescription() : null;
                    $thisRow['totAuthTrailers'] = $licence->getTotAuthTrailers();
                    $thisRow['totAuthVehicles'] = $licence->getTotAuthVehicles();

                    $thisRow['vehiclesInPossession'] = $licence->getActiveVehiclesCount();
                    $thisRow['trailersInPossession'] = $licence->getTotAuthTrailers();

                    $data[] = $thisRow;
                }
            }
        }

        return [
            'data' => [
                'tables' => [
                    'linked-licences-app-numbers' => $data
                ]
            ]
        ];
    }
}
