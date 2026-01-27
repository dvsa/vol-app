<?php

/**
 * Delete Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Workshop;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DeleteContactDetailsAndAddressTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Psr\Container\ContainerInterface;

/**
 * Delete Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    use DeleteContactDetailsAndAddressTrait;

    protected $repoServiceName = 'Workshop';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $id) {
            /** @var Workshop $workshop */
            $workshop = $this->getRepo()->fetchById($id);
            $this->maybeDeleteContactDetailsAndAddress($workshop->getContactDetails());
            $this->getRepo()->delete($workshop);
        }

        $result->addMessage(count($command->getIds()) . ' Workshop(s) removed');

        // create Event History record
        $this->eventHistoryCreator->create($workshop, EventHistoryTypeEntity::EVENT_CODE_DELETE_SAFETY_INSPECTOR);
        $this->eventHistoryCreator->create($workshop->getContactDetails(), EventHistoryTypeEntity::EVENT_CODE_DELETE_SAFETY_INSPECTOR, null,$workshop->getLicence());
        $this->eventHistoryCreator->create($workshop->getContactDetails()->getAddress(), EventHistoryTypeEntity::EVENT_CODE_DELETE_SAFETY_INSPECTOR, null, $workshop->getLicence());

        return $result;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
