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

/**
 * Create Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Workshop';

    protected $extraRepos = ['ContactDetails', 'Licence'];

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

        return $result;
    }
}
