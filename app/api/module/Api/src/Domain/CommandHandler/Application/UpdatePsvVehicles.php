<?php

/**
 * Update Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePsvVehicles as Cmd;

/**
 * Update Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdatePsvVehicles extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_PSV_VE_NO_ROWS = 'ERR_PSV_VE_NO_ROWS';

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['LicenceVehicle'];

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if (!$command->getPartial() && $command->getHasEnteredReg() === 'Y') {
            $licenceVehicles = $this->getRepo('LicenceVehicle')->getAllPsvVehicles($application);

            if ($licenceVehicles->isEmpty()) {
                throw new ValidationException(
                    [
                        'hasEnteredReg' => [
                            [self::ERR_PSV_VE_NO_ROWS => self::ERR_PSV_VE_NO_ROWS]
                        ]
                    ]
                );
            }
        }

        $application->setHasEnteredReg($command->getHasEnteredReg());

        $this->getRepo()->save($application);

        $this->result->addMessage('Application updated');

        $data = [
            'id' => $application->getId(),
            'section' => 'vehiclesPsv'
        ];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        return $this->result;
    }
}
