<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class OperatingCentres
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class OperatingCentres extends AbstractSection
{
    /**
     * Generate licence operating centres with sorted address
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $data = [];

        $ocRels = $this->sortOperatingCentresRelations(
            $this->getOperatingCentresRelations($case)
        );

        /** @var Entity\OperatingCentre\OperatingCentre $oc */
        foreach ($ocRels as $ocRel) {
            $oc = $ocRel->getOperatingCentre();

            $data[] = [
                'id' => $oc->getId(),
                'version' => $oc->getVersion(),
                'totAuthVehicles' => $ocRel->getNoOfVehiclesRequired(),
                'totAuthTrailers' => $ocRel->getNoOfTrailersRequired(),
                'OcAddress' => (
                    $oc->getAddress() !== null
                    ? $oc->getAddress()->toArray()
                    : []
                ),
            ];
        }

        return [
            'data' => [
                'tables' => [
                    'operating-centres' => $data
                ]
            ]
        ];
    }

    /**
     * Get operation centers relations with case
     *
     * @param CasesEntity $case Case entity
     *
     * @return array
     */
    private function getOperatingCentresRelations(CasesEntity $case)
    {
        $appOcs = [];
        $licOcs = $case->getLicence()->getOperatingCentres()->toArray();

        if ($case->getCaseType()->getId() === CasesEntity::APP_CASE_TYPE) {
            $appOcs = $case->getApplication()->getOperatingCentres()->toArray();
        }

        //  get operation centers relations to applications and licences
        $ocRels = array_merge($licOcs, $appOcs);

        /** @var Entity\Application\ApplicationOperatingCentre | Entity\Licence\LicenceOperatingCentre $ocRel */
        $result = [];

        foreach ($ocRels as $ocRel) {
            $oc = $ocRel->getOperatingCentre();
            $id = $oc->getId();

            if (
                $ocRel instanceof Entity\Application\ApplicationOperatingCentre
                && $ocRel->getAction() === Entity\Application\ApplicationOperatingCentre::ACTION_DELETE
            ) {
                unset($result[$id]);
            } else {
                $result[$id] = $ocRel;
            }
        }

        return $result;
    }

    /**
     * Sort Operation centers by address
     *
     * @param array $ocrs List of operation centers relations to applications and licences
     *
     * @return array
     */
    private function sortOperatingCentresRelations(array $ocrs)
    {
        if (count($ocrs) == 0) {
            return [];
        }

        //  sort
        $fncSort = function ($a, $b) {
            /** @var Entity\Application\ApplicationOperatingCentre | Entity\Licence\LicenceOperatingCentre $a */
            /** @var Entity\Application\ApplicationOperatingCentre | Entity\Licence\LicenceOperatingCentre $b */
            $aAddress = $a->getOperatingCentre()->getAddress();
            $aPostCode = ($aAddress !== null ? $aAddress->getPostcode() : '');

            $bAddress = $b->getOperatingCentre()->getAddress();
            $bPostCode = ($bAddress !== null ? $bAddress->getPostcode() : '');

            return strcmp($aPostCode, $bPostCode);
        };
        uasort($ocrs, $fncSort);

        return $ocrs;
    }
}
