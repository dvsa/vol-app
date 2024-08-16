<?php

/**
 * Save Address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress as Cmd;

/**
 * Save Address
 * Create or Update an address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class SaveAddress extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Address';

    protected $extraRepos = ['ContactDetails'];

    public function handleCommand(CommandInterface $command)
    {
        // Update Address
        if (!empty($command->getId())) {
            return $this->updateAddress($command);
        }

        return $this->createAddress($command);
    }

    private function createAddress(Cmd $command)
    {
        $result = new Result();

        $address = new Address();
        $this->populate($address, $command);
        $this->getRepo()->save($address);

        $contactType = $command->getContactType();
        if (!is_null($contactType)) {
            $contactDetails = new ContactDetails($this->getRepo()->getRefdataReference($contactType));
            $contactDetails->setAddress($address);
            $this->getRepo('ContactDetails')->save($contactDetails);
            $result->addId('contactDetails', $contactDetails->getId());
            $result->addMessage('Contact Details created');
        }

        $result->addId('address', $address->getId());
        $result->addMessage('Address created');
        $result->setFlag('hasChanged', true);

        return $result;
    }

    private function updateAddress(Cmd $command)
    {
        $result = new Result();

        /** @var Address $address */
        $address = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->populate($address, $command);

        $this->getRepo()->save($address);

        if ($address->getVersion() != $command->getVersion()) {
            $result->setFlag('hasChanged', true);
            $result->addMessage('Address updated');
        } else {
            $result->setFlag('hasChanged', false);
            $result->addMessage('Address unchanged');
        }

        return $result;
    }

    private function populate(Address $address, Cmd $command)
    {
        $countryCode = $command->getCountryCode();

        if (!empty($countryCode)) {
            $countryCode = $this->getRepo()->getReference(Country::class, $countryCode);
        } else {
            $countryCode = null;
        }

        $address->updateAddress(
            $command->getAddressLine1(),
            $command->getAddressLine2(),
            $command->getAddressLine3(),
            $command->getAddressLine4(),
            $command->getTown(),
            $command->getPostcode(),
            $countryCode
        );
    }
}
