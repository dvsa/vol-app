<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as Command;

/**
 * Create ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';
    protected $extraRepos = ['Cases'];

    /**
     * Command Handler
     *
     * @param Command $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->validate($command);

        // create entity with default values
        $conditionUndertaking = new ConditionUndertaking(
            $this->getRepo()->getRefdataReference($command->getType()),
            $command->getFulfilled(),
            'Y'
        );
        $conditionUndertaking
            ->setAttachedTo($this->getRepo()->getRefdataReference($command->getAttachedTo()))
            ->setNotes($command->getNotes())
            ->setConditionCategory($this->getRepo()->getRefdataReference($command->getConditionCategory()));

        // set operating centre
        if (!empty($command->getOperatingCentre())) {
            $conditionUndertaking->setOperatingCentre(
                $this->getRepo()->getReference(OperatingCentre::class, $command->getOperatingCentre())
            );
        } else {
            $conditionUndertaking->setOperatingCentre(null);
        }

        // if added via a case
        if (!empty($command->getCase())) {
            /* @var $case Cases */
            $case = $this->getRepo('Cases')->fetchById($command->getCase());
            $conditionUndertaking
                ->setAddedVia($this->getRepo()->getRefdataReference(ConditionUndertaking::ADDED_VIA_CASE))
                ->setCase($case)
                ->setIsDraft('N');
            if ($case->getApplication()) {
                $conditionUndertaking->setApplication($case->getApplication());
            }
            if ($case->getLicence()) {
                $conditionUndertaking->setLicence($case->getLicence());
            }
        }

        // if added via a licence
        if (!empty($command->getLicence())) {
            $conditionUndertaking
                ->setAddedVia($this->getRepo()->getRefdataReference(ConditionUndertaking::ADDED_VIA_LICENCE))
                ->setLicence($this->getRepo()->getReference(Licence::class, $command->getLicence()))
                ->setIsDraft('N');
        }

        // if added via an application
        if (!empty($command->getApplication())) {
            $conditionUndertaking
                ->setAddedVia($this->getRepo()->getRefdataReference(ConditionUndertaking::ADDED_VIA_APPLICATION))
                ->setApplication($this->getRepo()->getReference(Application::class, $command->getApplication()))
                ->setAction(ConditionUndertaking::ACTION_ADD);
        }
        $this->getRepo()->save($conditionUndertaking);

        $result = new Result();
        $result->addId('conditionUndertaking', $conditionUndertaking->getId());
        $result->addMessage('ConditionUndertaking created');

        return $result;
    }

    /**
     * Vaidate the command params
     *
     * @param Command $command Command
     *
     * @return void
     * @throws ValidationException
     */
    protected function validate(Command $command)
    {
        // if attached to an Operating Centre then operating centre param is mandatory
        if (
            $command->getAttachedTo() === ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE &&
            empty($command->getOperatingCentre())
        ) {
            throw new ValidationException(['Operating centre missing']);
        }

        // must be added by something
        if (empty($command->getApplication()) && empty($command->getLicence()) && empty($command->getCase())) {
            throw new ValidationException(['Application, Licence or Case must be specified']);
        }
    }
}
