<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;

/**
 * Merge Transport Manager
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Merge extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'TransportManager';
    protected $extraRepos = ['Task', 'Note', 'EventHistory'];

    /**
     * Record of changes made during the merge
     * @var array
     */
    protected $changes = [];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $donorTm TransportManager */
        $donorTm = $this->getRepo()->fetchById($command->getId());
        /* @var $recipientTm TransportManager */
        $recipientTm = $this->getRepo()->fetchById($command->getRecipientTransportManager());

        $confirm = $command->getConfirm();

        $this->validate($donorTm, $recipientTm, $confirm);

        $this->transferLva($donorTm, $recipientTm);
        $this->transferCases($donorTm, $recipientTm);
        $this->transferDocuments($donorTm, $recipientTm);
        $this->transferTasks($donorTm, $recipientTm);
        $this->transferNotes($donorTm, $recipientTm);
        $this->transferEventHistory($donorTm, $recipientTm);
        $this->clearEntityUserCaches($donorTm);
        $this->transferUserAccount($donorTm, $recipientTm);

        // record what has been merge on donarTm
        $donorTm->setMergeToTransportManager($recipientTm);
        $donorTm->setMergeDetails($this->getRecordChanges());

        // Populate the losing transport manager removed date
        $donorTm->setRemovedDate(new DateTime());

        $donorTm->setTmStatus(
            $this->getRepo()->getRefdataReference(TransportManager::TRANSPORT_MANAGER_STATUS_REMOVED)
        );

        $this->getRepo()->save($donorTm);

        $result = new Result();
        $result->addMessage(
            sprintf(
                'Merged Transport Manager id %d into TransportManager id %d.',
                $donorTm->getId(),
                $recipientTm->getId()
            )
        );

        return $result;
    }

    /**
     * Validate the donor and recipient Transport Managers
     *
     * @param bool $confirm
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    protected function validate(TransportManager $donorTm, TransportManager $recipientTm, $confirm)
    {
        $messages = [];
        if ($donorTm === $recipientTm) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['TM_MERGE_DONAR_RECIPIENT_SAME' => 'Cannot merge into self']
            );
        }

        // If both the losing and winning transport manager has a linked user account and there is no confirmation,
        // an error is raised
        if (!$donorTm->getUsers()->isEmpty() && !$recipientTm->getUsers()->isEmpty() && !$confirm) {
            $messages['TM_MERGE_BOTH_HAVE_USER_ACCOUNTS'] = 'Both transport managers have linked user accounts. You '
                . 'must remove one of the user accounts prior to merge.';
        }

        // check if already merged, dont allow
        if ($donorTm->getMergeToTransportManager()) {
            $messages['TM_MERGE_ALREADY_MERGED'] = 'Transport Manager has already been merged';
        }

        // check if recipientTm is not already removed
        if (!empty($recipientTm->getRemovedDate())) {
            $messages['TM_MERGE_RECIPIENT_REMOVED'] = 'Recipient Transport Manager has already been removed';
        }

        if (!empty($messages)) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException($messages);
        }
    }

    /**
     * Record what changes have been made
     *
     * @param string $entityName Entity name where merges are happening
     * @param int    $id         ID (PK) of entity that have been merged
     */
    protected function recordChange($entity)
    {
        $name = $this->getEntityName($entity);

        $this->changes[$name][] = $entity->getId();
    }

    /**
     * Get the name of an entity
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getEntityName($entity)
    {
        return $entity::class;
    }

    /**
     * Get all the changes that have been made as part of the merge
     *
     * @return array
     */
    protected function getRecordChanges()
    {
        return $this->changes;
    }

    /**
     * Transfer any associated licences, new applications or variations
     */
    protected function transferLva(TransportManager $donorTm, TransportManager $recipientTm)
    {
        /* @var $tml \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence */
        foreach ($donorTm->getTmLicences() as $tml) {
            $tml->setTransportManager($recipientTm);
            $this->recordChange($tml);
        }

        /* @var $tma \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication */
        foreach ($donorTm->getTmApplications() as $tma) {
            $tma->setTransportManager($recipientTm);
            $this->recordChange($tma);
        }
    }

    /**
     * Transfer any associated cases
     */
    protected function transferCases(TransportManager $donorTm, TransportManager $recipientTm)
    {
        /* @var $case \Dvsa\Olcs\Api\Entity\Cases\Cases */
        foreach ($donorTm->getCases() as $case) {
            $case->setTransportManager($recipientTm);
            $this->recordChange($case);
        }
    }

    /**
     * Transfer any associated documents
     */
    protected function transferDocuments(TransportManager $donorTm, TransportManager $recipientTm)
    {
        /* @var $document \Dvsa\Olcs\Api\Entity\Doc\Document */
        foreach ($donorTm->getDocuments() as $document) {
            $document->setTransportManager($recipientTm);
            $this->recordChange($document);
        }
    }

    /**
     * Transfer any associated tasks
     */
    protected function transferTasks(TransportManager $donorTm, TransportManager $recipientTm)
    {
        $tasks = $this->getRepo('Task')->fetchByTransportManager($donorTm);
        /* @var $task \Dvsa\Olcs\Api\Entity\Task\Task */
        foreach ($tasks as $task) {
            $task->setTransportManager($recipientTm);
            $this->recordChange($task);
        }
    }

    /**
     * Transfer any associated notes
     */
    protected function transferNotes(TransportManager $donorTm, TransportManager $recipientTm)
    {
        $notes = $this->getRepo('Note')->fetchByTransportManager($donorTm);
        /* @var $note \Dvsa\Olcs\Api\Entity\Note\Note */
        foreach ($notes as $note) {
            $note->setTransportManager($recipientTm);
            $this->recordChange($note);
        }
    }

    /**
     * Transfer any event history
     */
    protected function transferEventHistory(TransportManager $donorTm, TransportManager $recipientTm)
    {
        $eventHistorys = $this->getRepo('EventHistory')->fetchByTransportManager($donorTm);
        /* @var $eventHistory \Dvsa\Olcs\Api\Entity\EventHistory\EventHistory */
        foreach ($eventHistorys as $eventHistory) {
            $eventHistory->setTransportManager($recipientTm);
            $this->recordChange($eventHistory);
        }
    }

    /**
     * Transfer the loser's linked user account (if applicable)
     */
    protected function transferUserAccount(TransportManager $donorTm, TransportManager $recipientTm)
    {
        /* @var $user \Dvsa\Olcs\Api\Entity\User\User */
        foreach ($donorTm->getUsers() as $user) {
            $user->setTransportManager($recipientTm);
            $this->recordChange($user);
        }
    }
}
