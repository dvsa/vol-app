<?php

/**
 * Create Partner
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Partner
 */
final class CreatePartner extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Partner';

    protected $extraRepos = ['ContactDetails'];

    public function handleCommand(CommandInterface $command)
    {
        $partner = ContactDetails::create(
            $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_PARTNER),
            $this->getRepo('ContactDetails')->populateRefDataReference(
                $command->getArrayCopy()
            )
        );

        $this->getRepo()->save($partner);

        $result = new Result();
        $result->addId('partner', $partner->getId());
        $result->addMessage('Partner created successfully');

        return $result;
    }
}
