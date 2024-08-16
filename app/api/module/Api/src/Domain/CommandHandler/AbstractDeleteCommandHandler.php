<?php

/**
 * Delete Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Abstract
 */
abstract class AbstractDeleteCommandHandler extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName;
    protected $extraError;

    /**
     * Delete Command Handler Abstract
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        // would be better as an interface check but BC...
        if (method_exists($command, 'getIds')) {
            $ids = $command->getIds();
        } elseif (method_exists($command, 'getId')) {
            $ids = [$command->getId()];
        } else {
            $ids = [];
        }

        return $this->doDelete($ids);
    }

    /**
     * Do the delete
     *
     *
     * @return Result
     * @throws ValidationException
     */
    protected function doDelete(array $ids)
    {
        /** @var RepositoryInterface $repo */
        $repo = $this->getRepo();
        $result = new Result();
        $entities = [];

        foreach ($ids as $id) {
            try {
                $entity = $repo->fetchById($id);
                $this->checkDeletable($id, $entity);
                $entities[$id] = $entity;
            } catch (NotFoundException) {
                $result->addMessage(sprintf('Id %d not found', $id));
            }
        }

        // delete entities at the end so all can be confirmed as deletable beforehand
        foreach ($entities as $id => $entity) {
            $repo->delete($entity);
            $result->addId('id' . $id, $id);
            $result->addMessage(sprintf('Id %d deleted', $id));
        }

        return $result;
    }

    /**
     * Check the entity is deletable
     *
     * @param int|string   $id     the id of the entity
     * @param mixed        $entity the entity being deleted
     *
     * @return void
     * @throws ValidationException
     */
    protected function checkDeletable($id, mixed $entity): void
    {
        /** for BC reasons we need to check the interface, not everything has a canDelete method */
        if ($entity instanceof DeletableInterface && !$entity->canDelete()) {
            $messages = [
                sprintf('Id %d (%s) is not allowed to be deleted', $id, $this->repoServiceName)
            ];

            if (!empty($this->extraError)) {
                $messages[] = $this->extraError;
            }

            throw new ValidationException($messages);
        }
    }
}
