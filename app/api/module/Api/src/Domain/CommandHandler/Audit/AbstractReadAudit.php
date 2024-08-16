<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Abstract Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReadAudit extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const INTEGRITY_CONSTRAINT_VIOLATION_CODE = 23000;

    protected $recordClass;

    protected $entityRepo;

    public function handleCommand(CommandInterface $command)
    {
        $existsMessage = 'Audit record exists';
        if ($this->doesRecordExist($command)) {
            $this->result->addMessage($existsMessage);
            return $this->result;
        }

        if ($this->createRecord($command)) {
            $message = 'Audit record created';
        } else {
            $message = $existsMessage;
        }
        $this->result->addMessage($message);

        return $this->result;
    }

    /**
     * Grab an entity by id
     *
     * @param $id
     * @return mixed
     */
    protected function getEntity($id)
    {
        $this->extraRepos[] = $this->entityRepo;

        return $this->getRepo($this->entityRepo)->fetchById($id);
    }

    protected function doesRecordExist($command)
    {
        $userId = $this->getCurrentUser()->getId();

        /** @var \Dvsa\Olcs\Api\Domain\Repository\AbstractReadAudit $repo */
        $repo = $this->getRepo();

        return (!empty($repo->fetchOneOrMore($userId, $command->getId(), new \DateTime())));
    }

    protected function createRecord($command)
    {
        $className = $this->recordClass;

        $record = new $className($this->getCurrentUser(), $this->getEntity($command->getId()));
        try {
            $this->getRepo()->save($record);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if (empty($code)) {
                $previousException = $e->getPrevious();
                $code = $previousException->getCode();
            }
            if ((int) $code === self::INTEGRITY_CONSTRAINT_VIOLATION_CODE) {
                // that's fine, we already have an audit record, just tried to insert
                // two records at the same time and got "Integrity constraint violation"
                // we can ignore it.
                return false;
            }
            // otherwise re-throw an exception
            throw $e;
        }
        return true;
    }
}
