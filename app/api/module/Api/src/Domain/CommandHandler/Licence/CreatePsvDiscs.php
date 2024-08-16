<?php

/**
 * Create Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreatePsvDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PsvDisc';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $howMany = $command->getAmount();

        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->getReference(LicenceEntity::class, $command->getLicence());

        $totalAuth = $licence->getTotAuthVehicles();
        $currentDiscs = $licence->getPsvDiscsNotCeased()->count();

        if (($currentDiscs + $howMany) > $totalAuth) {
            throw new ValidationException(
                [
                    'amount' => [
                        PsvDisc::ERROR_CANT_EXCEED_TOT_AUTH => 'Number of discs cannot exceed total auth'
                    ]
                ]
            );
        }

        $this->getRepo()->createPsvDiscs($licence->getId(), $howMany, $command->getIsCopy() === 'Y');

        $result->addMessage($howMany . ' PSV Disc(s) created');

        return $result;
    }
}
