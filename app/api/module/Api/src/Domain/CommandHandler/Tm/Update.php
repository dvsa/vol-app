<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress as SaveAddressCmd;
use Dvsa\Olcs\Api\Domain\Command\Person\UpdateFull as UpdatePersonCmd;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\Tm\Update as UpdateTmCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepository;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

final class Update extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use QueueAwareTrait;
    use CacheAwareTrait;

    /**
     * @inheritDoc
     */
    protected $repoServiceName = 'TransportManager';

    /**
     * @inheritDoc
     */
    protected $extraRepos = ['ContactDetails'];

    /**
     * @param CommandInterface|UpdateTmCmd $command command to update a TM
     * @return Result
     * @throws RuntimeException
     * @throws NotFoundException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $updatePerson = UpdatePersonCmd::create(
            [
                'id'         => $command->getPersonId(),
                'version'    => $command->getPersonVersion(),
                'firstName'  => $command->getFirstName(),
                'lastName'   => $command->getLastName(),
                'title'      => $command->getTitle(),
                'birthDate'  => $command->getBirthDate(),
                'birthPlace' => $command->getBirthPlace()
            ]
        );
        $personResult = $this->handleSideEffect($updatePerson);

        $saveWorkAddress = SaveAddressCmd::create(
            [
                'id'           => $command->getWorkAddressId(),
                'version'      => $command->getWorkAddressVersion(),
                'addressLine1' => $command->getWorkAddressLine1(),
                'addressLine2' => $command->getWorkAddressLine2(),
                'addressLine3' => $command->getWorkAddressLine3(),
                'addressLine4' => $command->getWorkAddressLine4(),
                'town'         => $command->getWorkTown(),
                'postcode'     => $command->getWorkPostcode(),
                'countryCode'  => $command->getWorkCountryCode() ?: null,
                'contactType'  => ContactDetails::CONTACT_TYPE_TRANSPORT_MANAGER
            ]
        );
        $workAddressResult = $this->handleSideEffect($saveWorkAddress);
        $workCdId = $workAddressResult->getId('contactDetails');

        $result->merge($this->updateTransportManagerHomeContactDetails($command));

        $transportManager = $this->updateTransportManager($command, $workCdId);
        $this->clearEntityUserCaches($transportManager);

        $result->addId('transportManager', $transportManager->getId());
        $result->addMessage('Transport Manager updated successfully');
        $result->merge($personResult);

        if ($workAddressResult->getFlag('hasChanged')) {
            $result->addId('workAddress', $workAddressResult->getId('address'));
            $result->addMessage('Work address updated');
        }

        return $result;
    }

    /**
     * Updates the home contact details for a transport manager.
     *
     * @return Result
     * @throws NotFoundException
     * @throws RuntimeException
     */
    protected function updateTransportManagerHomeContactDetails(UpdateTmCmd $command): Result
    {
        $result = new Result();

        $homeAddressResult = $this->handleSideEffect(SaveAddressCmd::create([
            'id'           => $homeAddressId = $command->getHomeAddressId(),
            'version'      => $command->getHomeAddressVersion(),
            'addressLine1' => $command->getHomeAddressLine1(),
            'addressLine2' => $command->getHomeAddressLine2(),
            'addressLine3' => $command->getHomeAddressLine3(),
            'addressLine4' => $command->getHomeAddressLine4(),
            'town'         => $command->getHomeTown(),
            'postcode'     => $command->getHomePostcode(),
            'countryCode'  => $command->getHomeCountryCode(),
        ]));

        $contactDetailsRepository = $this->resolveContactDetailsRepository();
        $homeContactDetails = $contactDetailsRepository->fetchById($command->getHomeCdId());
        assert($homeContactDetails instanceof ContactDetails, 'Expected ContactDetails entity');

        $homeContactDetails->setEmailAddress($command->getEmailAddress());

        $newHomeAddressId = $homeAddressResult->getId('address');
        if (null !== $newHomeAddressId) {
            $homeAddress = $contactDetailsRepository->getReference(Address::class, $newHomeAddressId);
            assert($homeAddress instanceof Address, 'Expected instance of Address');
            $homeContactDetails->setAddress($homeAddress);
            $homeAddressId = $homeAddress->getId();
        }

        $contactDetailsRepository->save($homeContactDetails);

        if ($homeAddressResult->getFlag('hasChanged')) {
            $result->addId('homeAddress', $homeAddressId);
            $result->addMessage('Home address updated');
        }

        if (((int) $command->getHomeCdVersion()) !== $homeContactDetails->getVersion()) {
            $result->addId('homeContactDetails', $homeContactDetails->getId());
            $result->addMessage('Home contact details updated');
        }

        return $result;
    }

    /**
     * @return ContactDetailsRepository
     * @throws RuntimeException
     */
    protected function resolveContactDetailsRepository(): ContactDetailsRepository
    {
        return $this->getRepo('ContactDetails');
    }

    /**
     * Update a transport manager.
     *
     * @param UpdateTmCmd $command  command to update tm
     * @param int|null    $workCdId work contact details id
     * @return TransportManagerEntity
     * @throws RuntimeException
     */
    protected function updateTransportManager($command, $workCdId = null)
    {
        /** @var TransportManagerEntity $transportManager */
        $transportManager = $this->getRepo('TransportManager')->fetchById($command->getId());

        $transportManager->updateTransportManager(
            $this->getRepo()->getRefdataReference($command->getType()),
            $this->getRepo()->getRefdataReference($command->getStatus()),
            $workCdId ? $this->getRepo()->getReference(ContactDetails::class, $workCdId) : null
        );

        $this->getRepo('TransportManager')->save($transportManager);

        $this->result->merge(
            $this->handleSideEffect(
                $this->nysiisQueueCmd($transportManager->getId())
            )
        );

        return $transportManager;
    }
}
