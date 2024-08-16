<?php

/**
 * TmQualification / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as TmQualificationEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * TmQualification / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmQualification';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $tmQualification = $this->createTmQualificationObject($command);
        $this->getRepo()->save($tmQualification);

        $result->addId('TmQualification', $tmQualification->getId());
        $result->addMessage('TmQualification created successfully');

        return $result;
    }

    /**
     * @param CommandInterface $command
     * @return TmQualificationEntity
     */
    private function createTmQualificationObject($command)
    {
        $tmQualification = new TmQualificationEntity();

        $tmQualification->updateTmQualification(
            $this->getRepo()->getRefdataReference($command->getQualificationType()),
            $command->getSerialNo(),
            $command->getIssuedDate(),
            $this->getRepo()->getReference(CountryEntity::class, $command->getCountryCode()),
            $this->getRepo()->getReference(TransportManagerEntity::class, $command->getTransportManager())
        );
        return $tmQualification;
    }
}
