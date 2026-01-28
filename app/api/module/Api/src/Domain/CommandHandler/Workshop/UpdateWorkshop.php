<?php

/**
 * Update Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Workshop;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Psr\Container\ContainerInterface;

/**
 * Update Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Workshop';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Workshop $workshop */
        $workshop = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // Update the address
        $addressData = $command->getContactDetails()['address'];
        $addressResult = $this->handleSideEffect(SaveAddress::create($addressData));
        $this->result->merge($addressResult);

        $address = $workshop->getContactDetails()->getAddress();

        // Update the Contact Details
        $contactDetails = $workshop->getContactDetails();
        $contactDetails->setFao($command->getContactDetails()['fao']);

        // Update the workshop
        $workshop->setIsExternal($command->getIsExternal());
        $this->getRepo()->save($workshop);

        $this->result->addMessage('Workshop updated');
        $this->result->setFlag('hasChanged', ($command->getVersion() != $workshop->getVersion()));

        if ($command->getVersion() != $workshop->getVersion()) {
            // create Event History record
            $this->eventHistoryCreator->create($workshop, EventHistoryTypeEntity::EVENT_CODE_EDIT_SAFETY_INSPECTOR);
        }

        if ($contactDetails->getVersion() != $command->getContactDetails()['version']) {
            // create Event History record
            $this->eventHistoryCreator->create($contactDetails, EventHistoryTypeEntity::EVENT_CODE_EDIT_SAFETY_INSPECTOR, null, $workshop->getLicence());
        }

        if ($workshop->getContactDetails()->getAddress() != $addressData['version']) {
            // create Event History record
            $this->eventHistoryCreator->create($address, EventHistoryTypeEntity::EVENT_CODE_EDIT_SAFETY_INSPECTOR, null, $workshop->getLicence());
        }

        return $this->result;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
