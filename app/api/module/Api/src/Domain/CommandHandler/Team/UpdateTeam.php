<?php

/**
 * Update Team
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Team;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Team
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateTeam extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Team';

    protected $extraRepos = ['Printer'];

    public function handleCommand(CommandInterface $command)
    {
        $this->checkIfTeamAlreadyExists($command->getName(), $command->getId());

        $team = $this->getRepo()->fetchWithPrinters($command->getId(), \Doctrine\ORM\Query::HYDRATE_OBJECT);
        $this->processDefaultPrinter($team, $command);

        $team->setName($command->getName());
        $team->setDescription($command->getDescription());
        if ($command->getTrafficArea()) {
            $team->setTrafficArea(
                $this->getRepo()->getReference(TrafficAreaEntity::class, $command->getTrafficArea())
            );
        }

        $this->getRepo()->save($team);

        $result = new Result();
        $result->addId('team', $team->getId());
        $result->addMessage('Team updated successfully');

        return $result;
    }

    /**
     * Check whether a team with a such name already exist
     */
    protected function checkIfTeamAlreadyExists($name, $id)
    {
        $teams = $this->getRepo()->fetchByName($name);

        // found another team with the same name
        if (count($teams) && $teams[0]->getId() !== (int)$id) {
            throw new ValidationException([TeamEntity::ERROR_TEAM_EXISTS => 'Team with such a name already exists']);
        }
    }

    /**
     * Update default team printer if needed
     *
     * @param TeamEntity $team
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface $command
     */
    protected function processDefaultPrinter($team, $command)
    {
        $currentDefaultTeamPrinter = $team->getDefaultTeamPrinter();
        if (
            !$currentDefaultTeamPrinter ||
            ($currentDefaultTeamPrinter->getPrinter()->getId() !== $command->getDefaultPrinter())
        ) {
            $newDefaultPrinter = $this->getRepo('Printer')->fetchById($command->getDefaultPrinter());
            $team->updateDefaultPrinter($newDefaultPrinter);
        }
    }
}
