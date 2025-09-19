<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Create as Cmd;

/**
 * Create LetterInstance
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstance';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        $letterInstance = new LetterInstanceEntity();
        $letterInstance->setReference($command->getReference());
        
        // Set letter type (required)
        $letterType = $this->getRepo('LetterType')->fetchById($command->getLetterType());
        $letterInstance->setLetterType($letterType);
        
        // Set optional relations
        if ($command->getLicence() !== null) {
            $licence = $this->getRepo('Licence')->fetchById($command->getLicence());
            $letterInstance->setLicence($licence);
        }
        
        if ($command->getApplication() !== null) {
            $application = $this->getRepo('Application')->fetchById($command->getApplication());
            $letterInstance->setApplication($application);
        }
        
        if ($command->getCase() !== null) {
            $case = $this->getRepo('Cases')->fetchById($command->getCase());
            $letterInstance->setCase($case);
        }
        
        if ($command->getBusReg() !== null) {
            $busReg = $this->getRepo('BusReg')->fetchById($command->getBusReg());
            $letterInstance->setBusReg($busReg);
        }
        
        if ($command->getOrganisation() !== null) {
            $organisation = $this->getRepo('Organisation')->fetchById($command->getOrganisation());
            $letterInstance->setOrganisation($organisation);
        }
        
        if ($command->getTransportManager() !== null) {
            $transportManager = $this->getRepo('TransportManager')->fetchById($command->getTransportManager());
            $letterInstance->setTransportManager($transportManager);
        }
        
        if ($command->getIrfoOrganisation() !== null) {
            $irfoOrganisation = $this->getRepo('Organisation')->fetchById($command->getIrfoOrganisation());
            $letterInstance->setIrfoOrganisation($irfoOrganisation);
        }
        
        if ($command->getDocument() !== null) {
            $document = $this->getRepo('Document')->fetchById($command->getDocument());
            $letterInstance->setDocument($document);
        }
        
        if ($command->getSentOn() !== null) {
            $letterInstance->setSentOn(new \DateTime($command->getSentOn()));
        }
        
        if ($command->getStatus() !== null) {
            $status = $this->getRepo()->getRefdataReference($command->getStatus());
            $letterInstance->setStatus($status);
        }

        $this->getRepo()->save($letterInstance);

        $this->result->addId('letterInstance', $letterInstance->getId());
        $this->result->addMessage("Letter instance '{$letterInstance->getReference()}' created");
        
        return $this->result;
    }
}