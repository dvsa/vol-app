<?php

/**
 * Delete Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Delete Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class DeleteStatement extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Statement';

    protected $extraRepos = ['Document'];

    /**
     * Delete Statement
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $statement = $this->getRepo()->fetchUsingId(
            $command,
            Query::HYDRATE_OBJECT
        );

        $this->getRepo()->delete($statement);

        // Delete any documents linked to this statement
        $documents = $this->getRepo('Document')->fetchListForStatement($statement->getId());
        foreach ($documents as $document) {
            $this->getRepo('Document')->delete($document);
        }

        $result->addMessage('Statement deleted');

        return $result;
    }
}
