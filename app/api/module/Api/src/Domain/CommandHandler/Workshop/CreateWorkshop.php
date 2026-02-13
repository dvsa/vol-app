<?php

/**
 * Create Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Workshop;

use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Psr\Container\ContainerInterface;

/**
 * Create Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Workshop';

    protected $extraRepos = ['ContactDetails', 'Licence'];

    private EventHistoryCreator $eventHistoryCreator;

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Licence $licence */
        $licence = $this->getRepo('Licence')->fetchById($command->getLicence());

        // Create the address and contact details record
        $addressData = $command->getContactDetails()['address'];
        $addressData['contactType'] = ContactDetails::CONTACT_TYPE_WORKSHOP;
        $addressResult = $this->handleSideEffect(SaveAddress::create($addressData));
        $result->merge($addressResult);

        // Set the Fao on the contact details record
        $contactDetailsId = $addressResult->getId('contactDetails');

        /** @var ContactDetails $contactDetails */
        $contactDetails = $this->getRepo('ContactDetails')->fetchById($contactDetailsId);
        $contactDetails->setFao($command->getContactDetails()['fao']);
        $this->getRepo('ContactDetails')->save($contactDetails);

        // Create the workshop
        $workshop = new Workshop($licence, $contactDetails);
        $workshop->setIsExternal($command->getIsExternal());
        $this->getRepo()->save($workshop);

        $result->addId('workshop', $workshop->getId());
        $result->addMessage('Workshop created');

        // create Event History record
        $this->eventHistoryCreator->create($workshop, EventHistoryTypeEntity::EVENT_CODE_ADD_SAFETY_INSPECTOR);
        $this->eventHistoryCreator->create($contactDetails, EventHistoryTypeEntity::EVENT_CODE_ADD_SAFETY_INSPECTOR, null, $workshop->getLicence());
        $this->eventHistoryCreator->create($contactDetails->getAddress(), EventHistoryTypeEntity::EVENT_CODE_ADD_SAFETY_INSPECTOR, null, $workshop->getLicence());

        return $result;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($container, $requestedName, $options);
    }
}
