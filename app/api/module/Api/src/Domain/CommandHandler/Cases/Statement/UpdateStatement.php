<?php

/**
 * Update Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Statement;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\UpdateStatement as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class UpdateStatement extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Statement';

    protected $extraRepos = ['ContactDetails'];

    /**
     * Creates opposition  and associated entities
     *
     * @param CommandInterface|Cmd $command command
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $statement = $this->updateStatementObject($command);
        $result->addMessage('Statement updated');

        /** @var ContactDetails $repository */
        $repository = $this->getRepo('ContactDetails');
        $statement->getRequestorsContactDetails()->update(
            $repository->populateRefDataReference(
                $command->getRequestorsContactDetails()
            )
        );

        $result->addMessage('Contact details updated');

        $this->getRepo()->save($statement);

        $result->addId('Statement ', $statement->getId());
        $result->addId('Contact Details', $statement->getRequestorsContactDetails()->getId());

        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDate::create(
                    [
                        'statement' => $statement->getId()
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * Create the opposition object
     *
     * @param Cmd $command command
     *
     * @return Statement
     * @throws RuntimeException
     */
    private function updateStatementObject(Cmd $command)
    {
        /** @var Statement $statement */
        $statement = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $statement->setStatementType($this->getRepo()->getRefdataReference($command->getStatementType()));
        $statement->setVrm($command->getVrm());
        $statement->setRequestorsBody($command->getRequestorsBody());
        $statement->setStoppedDate(new \DateTime($command->getStoppedDate()));
        $statement->setRequestedDate(new \DateTime($command->getRequestedDate()));
        $statement->setAuthorisersDecision($command->getAuthorisersDecision());

        /** @var User $assignedCaseworker */
        $assignedCaseworker = $this->getRepo()->getReference(User::class, $command->getAssignedCaseworker());
        $statement->setAssignedCaseworker($assignedCaseworker);

        if ($command->getIssuedDate() !== null) {
            $statement->setIssuedDate(new \DateTime($command->getIssuedDate()));
        }

        // this is the statement.contact_type field
        $statement->setContactType($this->getRepo()->getRefdataReference($command->getContactType()));

        return $statement;
    }
}
