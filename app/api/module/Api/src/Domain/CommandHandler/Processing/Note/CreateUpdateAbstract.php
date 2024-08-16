<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;

/**
 * Create Update Abstract a Note
 */
abstract class CreateUpdateAbstract extends AbstractCommandHandler
{
    protected $repoServiceName = 'Note';

    /**
     * @return Entity\Note\Note
     */
    public function getNoteEntity(CommandInterface $command)
    {
        $entity = $this->retrieveEntity($command);

        if (method_exists($command, 'getApplication') && $command->getApplication() !== null) {

            /** @var Entity\Application\Application $application */
            $application = $this->getRepo()->getReference(
                Entity\Application\Application::class,
                $command->getApplication()
            );

            $entity->setApplication($application);

            if ($application->getLicence() !== null) {
                $entity->setLicence($application->getLicence());
            }

            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_APPLICATION));
        }

        if (method_exists($command, 'getBusReg') && $command->getBusReg() !== null) {

            /** @var Entity\Bus\BusReg $busReg */
            $busReg = $this->getRepo()->getReference(
                Entity\Bus\BusReg::class,
                $command->getBusReg()
            );

            $entity->setBusReg($busReg);

            if ($busReg->getLicence() !== null) {
                $entity->setLicence($busReg->getLicence());
            }

            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_BUS));
        }

        if (method_exists($command, 'getCase') && $command->getCase() !== null) {

            /** @var Entity\Cases\Cases $case */
            $case = $this->getRepo()->getReference(
                Entity\Cases\Cases::class,
                $command->getCase()
            );

            $entity->setCase($case);

            // an additional bit of functionality required to satisfy the business rule...
            // We must also set the licence if the case is a licence case...
            // This is because the case notes list should include all licence notes.
            if ($case->getLicence()) {
                $entity->setLicence($case->getLicence());
            }

            // an additional bit of functionality required to satisfy the business rule...
            // We must also set the licence if the case is a licence case...
            // This is because the case notes list should include all licence notes.
            if ($case->getTransportManager()) {
                $entity->setTransportManager($case->getTransportManager());
            }

            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_CASE));
        }

        if (method_exists($command, 'getLicence') && $command->getLicence() !== null) {
            $licence = $this->getRepo()->getReference(
                Entity\Licence\Licence::class,
                $command->getLicence()
            );

            $entity->setLicence($licence);
            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_LICENCE));
        }

        if (method_exists($command, 'getOrganisation') && $command->getOrganisation() !== null) {
            $org = $this->getRepo()->getReference(
                Entity\Organisation\Organisation::class,
                $command->getOrganisation()
            );

            $entity->setOrganisation($org);
            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_ORGANISATION));
        }

        if (method_exists($command, 'getTransportManager') && $command->getTransportManager() !== null) {
            $transportManager = $this->getRepo()->getReference(
                Entity\Tm\TransportManager::class,
                $command->getTransportManager()
            );

            $entity->setTransportManager($transportManager);
            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_TRANSPORT_MANAGER));
        }

        if (method_exists($command, 'getIrhpApplication') && $command->getIrhpApplication() !== null) {
            $irhpApplication = $this->getRepo()->getReference(
                Entity\Permits\IrhpApplication::class,
                $command->getIrhpApplication()
            );

            $entity->setIrhpApplication($irhpApplication);
            $entity->setNoteType($this->getRepo()->getRefdataReference(NoteEntity::NOTE_TYPE_PERMIT));
        }

        return $entity;
    }

    /**
     * @return Entity\Note\Note
     */
    abstract protected function retrieveEntity(CommandInterface $command);
}
