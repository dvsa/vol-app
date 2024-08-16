<?php

/**
 * Update Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class UpdatePreviousConviction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PreviousConviction';

    public function handleCommand(CommandInterface $command)
    {
        $conviction = $this->getRepo()
            ->fetchById(
                $command->getId(),
                \Doctrine\ORM\Query::HYDRATE_OBJECT,
                $command->getVersion()
            );

        if ($command->getTitle()) {
            $title = $this->getRepo()->getRefdataReference($command->getTitle());

            $conviction->setTitle($title);
        }
        if ($command->getForename()) {
            $conviction->setForename($command->getForename());
        }
        if ($command->getFamilyName()) {
            $conviction->setFamilyName($command->getFamilyName());
        }

        $conviction->setConvictionDate(
            new \DateTime($command->getConvictionDate())
        );
        $conviction->setCategoryText($command->getCategoryText());
        $conviction->setNotes($command->getNotes());
        $conviction->setCourtFpn($command->getCourtFpn());
        $conviction->setPenalty($command->getPenalty());

        $application = $conviction->getApplication();

        $result = new Result();

        $this->getRepo()->save($conviction);

        if ($application) {
            $result->merge(
                $this->updateApplicationCompletion($application->getId())
            );
        }

        $result->addId('previousConviction', $conviction->getId());
        $result->addMessage('Previous conviction updated');
        return $result;
    }

    private function updateApplicationCompletion($applicationId)
    {
        return $this->handleSideEffect(
            UpdateApplicationCompletion::create(['id' => $applicationId, 'section' => 'convictionsPenalties'])
        );
    }
}
