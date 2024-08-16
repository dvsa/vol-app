<?php

/**
 * Process Duplicate Vehicle Warnings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarning as ProcessDuplicateVehicleWarningCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Process Duplicate Vehicle Warnings
 *
 * @NOTE We don't implement transactioned interface here, as we want to control what is transactioned
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessDuplicateVehicleWarnings extends AbstractCommandHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    public function handleCommand(CommandInterface $command)
    {
        $results = $this->getRepo()->fetchQueuedForWarning();

        // Nothing to process
        if (empty($results)) {
            $this->result->addMessage('Nothing to process');
            return $this->result;
        }

        $count = 0;
        $countUnmarked = 0;
        $countFailed = 0;

        /** @var LicenceVehicle $licenceVehicle */
        foreach ($results as $key => $licenceVehicle) {
            $vrm = $licenceVehicle->getVehicle()->getVrm();
            $licence = $licenceVehicle->getLicence();

            $duplicates = $this->getRepo()->fetchDuplicates($licence, $vrm, false);

            // No longer attached to more than 1 licence
            if (empty($duplicates)) {
                $licenceVehicle->removeDuplicateMark();
                $this->getRepo()->save($licenceVehicle);
                $countUnmarked++;
                continue;
            }

            $data = [
                'id' => $licenceVehicle->getId()
            ];

            try {
                $this->result->merge($this->handleSideEffect(ProcessDuplicateVehicleWarningCmd::create($data)));
                $this->result->addMessage($licenceVehicle->getId() . ' succeeded');
                $count++;
            } catch (\Exception $ex) {
                $this->result->addMessage($licenceVehicle->getId() . ' failed: ' . $ex->getMessage());
                $countFailed++;
            }
        }

        $this->result->addMessage($count . ' letter(s) sent');
        $this->result->addMessage($countUnmarked . ' record(s) no longer duplicates');
        $this->result->addMessage($countFailed . ' failed record(s)');

        return $this->result;
    }
}
