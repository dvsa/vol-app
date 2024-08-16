<?php

/**
 * Create Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepository;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\CreateStatement as CreateStatementCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Statement;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\CreateStatement as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Statement
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateStatement extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Statement';

    protected $extraRepos = ['ContactDetails', 'Cases'];

    /**
     * Creates opposition  and associated entities
     *
     * @param CommandInterface|CreateStatementCommand $command command
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $contactDetails = $this->createContactDetailsObject($command);
        $result->addMessage('Contact details created');

        $statement = $this->createStatementObject($command);
        $result->addMessage('Statement created');
        $statement->setRequestorsContactDetails($contactDetails);

        $this->getRepo()->save($statement);

        $result->addId('Statement ', $statement->getId());
        $result->addId('contactDetails', $contactDetails->getId());

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
     * Create the contact details object
     *
     * @param CommandInterface|CreateStatementCommand $command command
     *
     * @return ContactDetails
     * @throws RuntimeException
     */
    private function createContactDetailsObject($command)
    {
        /** @var ContactDetailsRepository $repository */
        $repository = $this->getRepo('ContactDetails');
        return ContactDetails::create(
            $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_STATEMENT_REQUESTOR),
            $repository->populateRefDataReference(
                $command->getRequestorsContactDetails()
            )
        );
    }

    /**
     * Create the opposition object
     *
     * @param Cmd $command command
     *
     * @return Statement
     * @throws RuntimeException
     */
    private function createStatementObject(Cmd $command)
    {
        /** @var Cases $case */
        $case = $this->getRepo()->getReference(Cases::class, $command->getCase());

        $statement = new Statement(
            $case,
            $this->getRepo()->getRefdataReference($command->getStatementType())
        );

        $statement->setLicenceNo($case->getLicence()->getLicNo());

        if (!is_null($case->getLicence()->getLicenceType())) {
            $statement->setLicenceType($case->getLicence()->getLicenceType());
        }

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

        if ($command->getContactType() !== null) {
            // this is the statement.contact_type field
            $statement->setContactType($this->getRepo()->getRefdataReference($command->getContactType()));
        }

        return $statement;
    }
}
