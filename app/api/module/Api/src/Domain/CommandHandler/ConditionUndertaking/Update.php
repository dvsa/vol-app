<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update as Command;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;

/**
 * Update ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        $this->validate($command);

        /* @var $conditionUndertaking ConditionUndertaking */
        $conditionUndertaking = $this->getRepo()->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        $conditionUndertaking
            ->setConditionType($this->getRepo()->getRefdataReference($command->getType()))
            ->setAttachedTo($this->getRepo()->getRefdataReference($command->getAttachedTo()))
            ->setIsFulfilled($command->getFulfilled())
            ->setNotes($command->getNotes())
            ->setConditionCategory($this->getRepo()->getRefdataReference($command->getConditionCategory()));

        $oc = empty($command->getOperatingCentre()) ? null :
            $this->getRepo()->getReference(OperatingCentre::class, $command->getOperatingCentre());
        $conditionUndertaking->setOperatingCentre($oc);

        $this->getRepo()->save($conditionUndertaking);

        $result = new Result();
        $result->addId('conditionUndertaking', $conditionUndertaking->getId());
        $result->addMessage('ConditionUndertaking updated');

        return $result;
    }

    /**
     * Vaidate the command params
     *
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
    }
}
