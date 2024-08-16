<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Calls an entity method based on the config
 * Designed for "one question per page"
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AbstractCallEntityMethod extends AbstractCommandHandler
{
    protected $repoServiceName = 'changeMe';
    protected $entityMethodName = 'changeMe';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $entityMethod = $this->entityMethodName;

        /** @var RepositoryInterface $repo */
        $repo = $this->getRepo($this->repoServiceName);

        //fetch the record from the DB
        $recordObject = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT);

        //call update method on the entity
        $recordObject->$entityMethod();

        //save the record
        $repo->save($recordObject);

        $this->result->addId($this->repoServiceName, $recordObject->getId());
        $this->result->addMessage($this->repoServiceName . ' updated');

        return $this->result;
    }
}
