<?php

/**
 * Update Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSafety as Cmd;

/**
 * Update Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateSafety extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->updateSafetyDetails($licence, $command);

        $this->getRepo()->save($licence);

        $result->addMessage('Licence updated');

        return $result;
    }

    private function updateSafetyDetails(Licence $licence, Cmd $command)
    {
        $tachoIns = $command->getTachographIns();

        if (!empty($tachoIns)) {
            $tachoIns = $this->getRepo()->getRefdataReference($command->getTachographIns());
        }

        // set safetyInsTrailers to null if the licence can't have trailer
        $safetyInsTrailers = null;

        if ($licence->canHaveTrailer()) {
            $safetyInsTrailers = $licence->getTotAuthTrailers() === 0 ? 0 : (int)$command->getSafetyInsTrailers();
        }

        $licence->updateSafetyDetails(
            $command->getSafetyInsVehicles(),
            $safetyInsTrailers,
            $tachoIns,
            $command->getTachographInsName(),
            $command->getSafetyInsVaries()
        );
    }
}
