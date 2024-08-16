<?php

/**
 * Update TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Create as CreateCommand;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;

/**
 * Update TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmEmployment';

    protected $extraRepos = ['TransportManagerApplication'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\TmEmployment\Update */

        /* @var $tmEmployment TmEmployment */
        $tmEmployment = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $tmEmployment->setPosition($command->getPosition());
        $tmEmployment->setHoursPerWeek($command->getHoursPerWeek());
        $tmEmployment->setEmployerName($command->getEmployerName());
        $this->getRepo()->save($tmEmployment);

        $updateContactResult = $this->updateContactDetails($tmEmployment->getContactDetails()->getAddress(), $command);

        $result = new Result();
        $result->merge($updateContactResult);
        $result->addMessage("Tm Employment ID {$tmEmployment->getId()} updated");

        return $result;
    }

    /**
     * Create Contact Details entity
     *
     * @param CreateCommand $command
     *
     * @return Result
     */
    protected function updateContactDetails(Address $address, \Dvsa\Olcs\Transfer\Command\TmEmployment\Update $command)
    {
        $response = $this->handleSideEffect(
            SaveAddress::create(
                [
                    'id' => $address->getId(),
                    'version' => $command->getAddress()['version'],
                    'addressLine1' => $command->getAddress()['addressLine1'],
                    'addressLine2' => $command->getAddress()['addressLine2'],
                    'addressLine3' => $command->getAddress()['addressLine3'],
                    'addressLine4' => $command->getAddress()['addressLine4'],
                    'town' => $command->getAddress()['town'],
                    'postcode' => $command->getAddress()['postcode'],
                    'countryCode' => $command->getAddress()['countryCode'],
                ]
            )
        );

        return $response;
    }
}
