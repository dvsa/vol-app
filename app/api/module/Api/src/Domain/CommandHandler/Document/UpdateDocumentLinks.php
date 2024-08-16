<?php

/**
 * Update Document Links
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as Cmd;
use Dvsa\Olcs\Api\Entity;

/**
 * Update Document Links
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateDocumentLinks extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Document';

    /**
     * Handle ccommand
     *
     * @param CommandInterface $command DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $document = $this->getRepo()->fetchUsingId($command);

        $this->linkDocument($document, $command);

        $this->getRepo()->save($document);

        return $this->result;
    }

    /**
     * Link a document to other entities
     *
     * @param Entity\Doc\Document $document Document
     * @param Cmd                 $command  DTO
     *
     * @return void
     */
    private function linkDocument(Entity\Doc\Document $document, Cmd $command)
    {
        $this->maybeLinkDocument($document, $command, Entity\Application\Application::class, 'Application');
        $this->maybeLinkDocument($document, $command, Entity\Bus\BusReg::class, 'BusReg');
        $this->maybeLinkDocument($document, $command, Entity\Cases\Cases::class, 'Case');
        $this->maybeLinkDocument($document, $command, Entity\Cases\Statement::class, 'Statement');
        $this->maybeLinkDocument($document, $command, Entity\Licence\Licence::class, 'Licence');
        $this->maybeLinkDocument($document, $command, Entity\Organisation\Organisation::class, 'IrfoOrganisation');
        $this->maybeLinkDocument($document, $command, Entity\Submission\Submission::class, 'Submission');
        $this->maybeLinkDocument($document, $command, Entity\TrafficArea\TrafficArea::class, 'TrafficArea');
        $this->maybeLinkDocument($document, $command, Entity\Tm\TransportManager::class, 'TransportManager');
        $this->maybeLinkDocument($document, $command, Entity\OperatingCentre\OperatingCentre::class, 'OperatingCentre');
        $this->maybeLinkDocument($document, $command, Entity\Licence\ContinuationDetail::class, 'ContinuationDetail');
        $this->maybeLinkDocument($document, $command, Entity\Permits\IrhpApplication::class, 'IrhpApplication');
        $this->maybeLinkDocument($document, $command, Entity\Surrender::class, 'Surrender');
        $this->maybeLinkDocument($document, $command, Entity\Messaging\MessagingConversation::class, 'MessagingConversation');
        $this->maybeLinkDocument($document, $command, Entity\Messaging\MessagingMessage::class, 'MessagingMessage');
    }

    /**
     * Link document to entity, if the DTO object has an ID reference to that entity
     *
     * @param Entity\Doc\Document $document Document
     * @param Cmd                 $command  DTO
     * @param string              $entity   Class name of entity
     * @param string              $suffix   Suffix of DTO method to get value for the entity
     *
     * @return void
     */
    private function maybeLinkDocument(Entity\Doc\Document $document, Cmd $command, $entity, $suffix)
    {
        $getter = 'get' . $suffix;
        $setter = 'set' . $suffix;
        $value = $command->{$getter}();

        if ($value !== null) {
            $this->result->addMessage('Document linked to ' . $suffix . ': ' . $value);
            $reference = $this->getRepo()->getReference($entity, $value);
            $document->{$setter}($reference);
        }
    }
}
