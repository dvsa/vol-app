<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\Overview as OverviewCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as VariationGrantCmd;

/**
 * AutoGrant
 *
 * Handles auto-granting of eligible variations. A variation qualifies when it
 * contains only operating centre removals and at least one operating centre
 * remains on the licence afterwards.
 *
 * Flow:
 *   1. Mark the application as auto-granted
 *   2. Set all caseworker tracking sections to "Accepted" via Overview
 *   3. Delegate to the standard Variation\Grant command
 */
final class AutoGrant extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $application->setWasAutoGranted(true);
        $this->getRepo()->save($application);

        $this->result->merge($this->completeTracking($application));
        $this->result->merge($this->grantVariation($command->getId()));

        $this->result->addMessage('Application auto-granted');
        $this->result->setFlag('autoGranted', true);

        return $this->result;
    }

    /**
     * Set all caseworker tracking sections to "Accepted"
     */
    private function completeTracking(ApplicationEntity $application): Result
    {
        $tracking = $application->getApplicationTracking();
        $accepted = (string) ApplicationTracking::STATUS_ACCEPTED;

        $sections = [
            'addresses',
            'businessDetails',
            'businessType',
            'communityLicences',
            'conditionsUndertakings',
            'operatingCentres',
            'people',
            'safety',
            'transportManagers',
            'typeOfLicence',
            'declarationsInternal',
            'vehicles'
        ];

        $trackingData = [
            'id'      => $tracking?->getId(),
            'version' => $tracking?->getVersion(),
        ];

        foreach ($sections as $section) {
            $trackingData[$section . 'Status'] = $accepted;
        }

        return $this->handleSideEffectAsSystemUser(
            OverviewCmd::create([
                'id'                      => $application->getId(),
                'version'                 => $application->getVersion(),
                'tracking'                => $trackingData,
                'overrideOppositionDate'  => $application->getOverrideOoo() ? 'Y' : 'N',
                'applicationReferredToPi' => $application->getApplicationReferredToPi() ?? 'N',
            ])
        );
    }

    /**
     * Delegate to the standard Variation\Grant command.
     * grantAuthority is always TC for auto-grants.
     */
    private function grantVariation(int $id): Result
    {
        return $this->handleSideEffectAsSystemUser(
            VariationGrantCmd::create([
                'id'             => $id,
                'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
            ])
        );
    }
}
