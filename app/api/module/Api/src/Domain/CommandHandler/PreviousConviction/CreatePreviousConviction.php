<?php

/**
 * Create Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\CreatePreviousConviction as Cmd;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class CreatePreviousConviction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PreviousConviction';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $conviction = $this->createPreviousConvictionObject($command);
        $result->addMessage('Previous conviction created');

        $this->getRepo()->save($conviction);

        $result->addId('previousConviction', $conviction->getId());

        if ($command->getApplication()) {
            $result->merge($this->updateApplicationCompletion($command->getApplication()));
        }

        return $result;
    }

    private function updateApplicationCompletion($applicationId)
    {
        return $this->handleSideEffect(
            UpdateApplicationCompletion::create(['id' => $applicationId, 'section' => 'convictionsPenalties'])
        );
    }

    /**
     * @return Application
     */
    private function createPreviousConvictionObject(Cmd $command)
    {
        $conviction = new PreviousConviction();

        if ($command->getTitle()) {
            $conviction->setTitle(
                $this->getRepo()->getRefdataReference($command->getTitle())
            );
        }
        if ($command->getTransportManager()) {
            $conviction->setTransportManager(
                $this->getRepo()->getReference(
                    \Dvsa\Olcs\Api\Entity\Tm\TransportManager::class,
                    $command->getTransportManager()
                )
            );
        }
        if ($command->getApplication()) {
            $conviction->setApplication(
                $this->getRepo()->getReference(Application::class, $command->getApplication())
            );
        }
        if ($command->getForename()) {
            $conviction->setForename($command->getForename());
        }
        if ($command->getFamilyName()) {
            $conviction->setFamilyName($command->getFamilyName());
        }
        $conviction->setConvictionDate(
            new DateTime($command->getConvictionDate())
        );
        $conviction->setCategoryText($command->getCategoryText());
        $conviction->setNotes($command->getNotes());
        $conviction->setCourtFpn($command->getCourtFpn());
        $conviction->setPenalty($command->getPenalty());

        return $conviction;
    }
}
