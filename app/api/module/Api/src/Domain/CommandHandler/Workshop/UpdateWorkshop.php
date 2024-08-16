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

        // Update the Contact Details
        $contactDetails = $workshop->getContactDetails();
        $contactDetails->setFao($command->getContactDetails()['fao']);

        // Update the workshop
        $workshop->setIsExternal($command->getIsExternal());
        $this->getRepo()->save($workshop);

        $this->result->addMessage('Workshop updated');
        $this->result->setFlag('hasChanged', ($command->getVersion() != $workshop->getVersion()));

        return $this->result;
    }
}
