<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Class AuthRequestedAppliedFor
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class AuthRequestedAppliedFor extends AbstractSection
{
    /**
     * Generate AuthRequestedAppliedFor Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $data = [];
        $licence = $case->getLicence();
        $applications = !empty($licence) ?
            $licence->getApplicationsByStatus([Application::APPLICATION_STATUS_UNDER_CONSIDERATION]) : [];

        $activeVehiclesCount = $licence->getActiveVehiclesCount();
        $trailersInPossession = $licence->getTrailersInPossession();
        $totAuthVehicles = $licence->getTotAuthVehicles();
        $totAuthTrailers = $licence->getTotAuthTrailers();

        /** @var Application $application */
        foreach ($applications as $application) {
            $thisData = [];
            $thisData['id'] = $application->getId();
            $thisData['version'] = $application->getVersion();
            $thisData['currentVehiclesInPossession'] = !empty($activeVehiclesCount) ? $activeVehiclesCount : '0';
            $thisData['currentTrailersInPossession'] = !empty($trailersInPossession) ? $trailersInPossession : '0';
            $thisData['currentVehicleAuthorisation'] = !empty($totAuthVehicles) ? $totAuthVehicles : '0';
            $thisData['currentTrailerAuthorisation'] = !empty($totAuthTrailers) ? $totAuthTrailers : '0';
            $thisData['requestedVehicleAuthorisation'] =
                !empty($application->getTotAuthVehicles()) ? $application->getTotAuthVehicles() : '0';
            $thisData['requestedTrailerAuthorisation'] =
                !empty($application->getTotAuthTrailers()) ? $application->getTotAuthTrailers() : '0';
            $data[] = $thisData;
        }

        return [
            'data' => [
                'tables' => [
                    'auth-requested-applied-for' => $data
                ]
            ]
        ];
    }
}
