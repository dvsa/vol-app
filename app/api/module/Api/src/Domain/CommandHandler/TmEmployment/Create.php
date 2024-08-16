<?php

/**
 * Create TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Create as CreateCommand;

/**
 * Create TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmEmployment';

    protected $extraRepos = ['TransportManagerApplication'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command CreateCommand */

        $tmEmployment = new TmEmployment();
        $tmEmployment->setPosition($command->getPosition());
        $tmEmployment->setHoursPerWeek($command->getHoursPerWeek());
        $tmEmployment->setEmployerName($command->getEmployerName());

        if (is_numeric($command->getTmaId())) {
            /* @var $tma TransportManagerApplication */
            $tma = $this->getRepo('TransportManagerApplication')->fetchById($command->getTmaId());
            $tmEmployment->setTransportManager($tma->getTransportManager());
        } elseif (is_numeric($command->geTransportManager())) {
            $tmEmployment->setTransportManager(
                $this->getRepo()->getReference(TransportManager::class, $command->geTransportManager())
            );
        } else {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['TransportManagerApplication ID or TransportManager ID must be specified']
            );
        }

        $createContactResult = $this->createContactDetails($command);
        $tmEmployment->setContactDetails(
            $this->getRepo()->getReference(
                ContactDetails::class,
                $createContactResult->getId('contactDetails')
            )
        );

        $this->getRepo()->save($tmEmployment);

        $result = new Result();
        $result->merge($createContactResult);
        $result->addId(lcfirst((string) $this->repoServiceName), $tmEmployment->getId());
        $result->addMessage("Tm Employment ID {$tmEmployment->getId()} created");

        return $result;
    }

    /**
     * Create Contact Details entity
     *
     *
     * @return Result
     */
    protected function createContactDetails(CreateCommand $command)
    {
        $response = $this->handleSideEffect(
            SaveAddress::create(
                [
                    'addressLine1' => $command->getAddress()['addressLine1'],
                    'addressLine2' => $command->getAddress()['addressLine2'],
                    'addressLine3' => $command->getAddress()['addressLine3'],
                    'addressLine4' => $command->getAddress()['addressLine4'],
                    'town' => $command->getAddress()['town'],
                    'postcode' => $command->getAddress()['postcode'],
                    'countryCode' => $command->getAddress()['countryCode'],
                    'contactType' => ContactDetails::CONTACT_TYPE_TRANSPORT_MANAGER,
                ]
            )
        );

        return $response;
    }
}
