<?php

/**
 * Update Partner
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Partner
 */
final class UpdatePartner extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Partner';

    protected $extraRepos = ['ContactDetails'];

    public function handleCommand(CommandInterface $command)
    {
        /** @var ContactDetails $partner */
        $partner = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $partner->update(
            $this->getRepo('ContactDetails')->populateRefDataReference(
                $command->getArrayCopy()
            )
        );

        $this->getRepo()->save($partner);

        $result = new Result();
        $result->addId('partner', $partner->getId());
        $result->addMessage('Partner updated successfully');

        return $result;
    }
}
