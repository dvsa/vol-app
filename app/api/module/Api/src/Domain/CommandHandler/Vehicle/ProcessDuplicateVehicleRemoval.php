<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\RemoveDuplicateVehicle as RemoveDuplicateVehicleCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Olcs\Logging\Log\Logger;

/**
 * Process Duplicate Vehicle Removal
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ProcessDuplicateVehicleRemoval extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    protected $repoServiceName = 'LicenceVehicle';

    protected $extraRepos = ['SystemParameter'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $results = $this->getRepo()->fetchForRemoval();

        if (empty($results)) {
            $this->result->addMessage('Nothing to process');
            return $this->result;
        }

        $count = 0;
        $countRemoved = 0;
        $countFailed = 0;
        $removedVehicles = [];

        /** @var LicenceVehicle $licenceVehicle */
        foreach ($results as $key => $licenceVehicle) {
            $vrm = $licenceVehicle->getVehicle()->getVrm();
            $licence = $licenceVehicle->getLicence();

            $duplicates = $this->getRepo()->fetchDuplicates($licence, $vrm, false);

            // No longer attached to more than 1 licence
            if (empty($duplicates)) {
                $licenceVehicle->removeDuplicateMark(true);
                $this->getRepo()->save($licenceVehicle);
                $countRemoved++;
                continue;
            }

            $licenceVehicleId = $licenceVehicle->getId();

            $data = [
                'id' => $licenceVehicleId
            ];
            try {
                $this->result->merge($this->handleSideEffect(RemoveDuplicateVehicleCmd::create($data)));
                $this->result->addMessage($licenceVehicleId . ' succeeded');
                $count++;
                $removedVehicles[] = [
                    'vrm' => $vrm,
                    'licNo' => $licence->getLicNo()
                ];
            } catch (\Exception $ex) {
                $this->result->addMessage($licenceVehicleId . ' failed: ' . $ex->getMessage());
                $countFailed++;
            }
        }
        $this->sendReport($removedVehicles);

        $this->result->addMessage($count . ' vehicle(s) removed');
        $this->result->addMessage($countRemoved . ' record(s) no longer duplicates');
        $this->result->addMessage($countFailed . ' failed record(s)');

        return $this->result;
    }

    /**
     * Send report
     *
     * @param array $removedVehicles removed vehicles
     *
     * @return void
     */
    protected function sendReport(array $removedVehicles)
    {
        if (count($removedVehicles) === 0) {
            return;
        }
        $emailAddress = $this->getRepo('SystemParameter')->fetchValue(SystemParameter::DUPLICATE_VEHICLE_EMAIL_LIST);
        if (empty($emailAddress)) {
            return;
        }
        usort(
            $removedVehicles,
            fn($a, $b) => $a['vrm'] <=> $b['vrm']
        );
        try {
            $message = new \Dvsa\Olcs\Email\Data\Message(
                $emailAddress,
                'email.duplicate-vehicles-removal.subject'
            );

            $this->sendEmailTemplate(
                $message,
                'email-duplicate-vehicles-removal',
                [
                    'removedVehicles' => $removedVehicles,
                ]
            );
            $this->result->addMessage('Removed vehicle list successfully sent to ' . $emailAddress);
        } catch (\Exception $e) {
            $errorMessage = 'Error sending removed vehicle list to ' . $emailAddress;
            $this->result->addMessage($errorMessage);

            Logger::err(
                'Error sending removed vehicle list to ' . $emailAddress,
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        }
    }
}
