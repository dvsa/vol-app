<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople as DeletePeopleCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation as CreateVariationCommand;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeopleViaVariation as DeletePeopleViaVariationCommand;
use Dvsa\Olcs\Transfer\Command\Variation\GrantDirectorChange as GrantDirectorChangeCommand;

/**
 * Delete People via a variation in a single transaction
 */
final class DeletePeopleViaVariation extends AbstractCommandHandler implements TransactionedInterface
{
    /**
     * Handle command
     *
     * @param DeletePeopleViaVariationCommand|CommandInterface $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $createVariationResult = $this->handleSideEffect(
            CreateVariationCommand::create(
                [
                    'id' => $command->getId(),
                    'variationType' => Application::VARIATION_TYPE_DIRECTOR_CHANGE,
                ]
            )
        );

        $variationId = $createVariationResult->getId('application');

        $this->handleSideEffect(
            DeletePeopleCommand::create(['id' => $variationId, 'personIds' => $command->getPersonIds()])
        );

        return $this->handleSideEffect(GrantDirectorChangeCommand::create(['id' => $variationId]));
    }
}
