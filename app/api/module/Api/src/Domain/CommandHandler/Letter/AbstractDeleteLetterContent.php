<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a letter appendix, issue, to-do or section.
 *
 * Refused while something live still uses it. Soft-deleted while generated letters point at it,
 * so they keep their content, otherwise deleted with everything it owns.
 */
abstract class AbstractDeleteLetterContent extends AbstractCommandHandler implements TransactionedInterface
{
    /** Message key the admin screens look for to show why a delete was refused */
    public const ERROR_KEY = 'letterDelete';

    /** What it is, as the admin knows it, e.g. "appendix" */
    protected string $contentName;

    /** What can be using it, e.g. "letter type" */
    protected string $usedByName = 'letter type';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $id = (int) $command->getId();
        $repo = $this->getRepo();

        try {
            $entity = $repo->fetchById($id);
        } catch (NotFoundException) {
            $this->result->addMessage(sprintf('Id %d not found', $id));

            return $this->result;
        }

        $this->checkDeletable($entity);

        $usedBy = $this->fetchUsedBy($id);

        if ($usedBy !== []) {
            throw new ValidationException([
                self::ERROR_KEY => sprintf(
                    'Cannot delete this %s because it is used by %s: %s. Remove it from the %s first.',
                    $this->contentName,
                    $this->usedByName,
                    implode(', ', $usedBy),
                    $this->usedByName
                ),
            ]);
        }

        if ($this->isReferenced($id)) {
            $repo->softDelete($entity);
        } else {
            $repo->hardDelete($id);
        }

        $this->result->addId('id' . $id, $id);
        $this->result->addMessage(sprintf('Id %d deleted', $id));

        return $this->result;
    }

    /**
     * Throw a ValidationException keyed by ERROR_KEY if this one can never be deleted
     *
     * @param mixed $entity
     * @return void
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function checkDeletable(mixed $entity): void
    {
    }

    /**
     * Names of whatever still uses it and so blocks the delete
     *
     * @param int $id
     * @return string[]
     */
    abstract protected function fetchUsedBy(int $id): array;

    /**
     * Whether generated letters (or old versions) still point at it, so the rows have to stay
     *
     * @param int $id
     * @return bool
     */
    abstract protected function isReferenced(int $id): bool;
}
