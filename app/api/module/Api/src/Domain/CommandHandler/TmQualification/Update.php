<?php

/**
 * TmQualification / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;

/**
 * TmQualification / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmQualification';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $tmQualification = $this->getRepo()->fetchById($command->getId());
        $tmQualification->updateTmQualification(
            $this->getRepo()->getRefdataReference($command->getQualificationType()),
            $command->getSerialNo(),
            $command->getIssuedDate(),
            $this->getRepo()->getReference(Country::class, $command->getCountryCode())
        );
        $this->getRepo()->save($tmQualification);

        $result->addId('tmQualification', $tmQualification->getId());
        $result->addMessage('TmQualification updated successfully');

        return $result;
    }
}
