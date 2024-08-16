<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Domain\Repository\AbstractReadonlyRepository;
use Dvsa\Olcs\Api\Domain\Exception;

/**
 * Unmerge Transport Manager
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Unmerge extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManager';
    protected $extraRepos = [
        'TransportManagerApplication',
        'TransportManagerLicence',
        'Cases',
        'Document',
        'Task',
        'Note',
        'EventHistory',
        'User'
    ];

    /**
     * Record of changes made during the merge
     *
     * @var array
     */
    protected $changes = [];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $tm TransportManager */
        $tm = $this->getRepo()->fetchById($command->getId());

        $this->validate($tm);

        $this->unmerge($tm);

        // Remove the previous merge details
        $tm->setMergeToTransportManager(null);
        $tm->setMergeDetails(null);

        // Remove the removed date from the current transport manager
        $tm->setRemovedDate(null);
        $tm->setTmStatus(
            $this->getRepo()->getRefdataReference(TransportManager::TRANSPORT_MANAGER_STATUS_CURRENT)
        );
        $this->getRepo()->save($tm);

        $result = new Result();
        $result->addMessage(
            sprintf(
                'Unmerged Transport Manager id %d',
                $tm->getId()
            )
        );

        return $result;
    }

    /**
     * Validate the donor and recipient Transport Managers
     *
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     * @return void
     */
    protected function validate(TransportManager $tm)
    {
        if (empty($tm->getMergeToTransportManager())) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['TM_UNMERGE_NOT_MERGED' => 'Cannot unmerge as TM was not merged']
            );
        }
    }

    /**
     * Unmerge all the entities that previously were associated with the TM
     *
     * @return void
     */
    protected function unmerge(TransportManager $tm)
    {
        $mergeDetails = $tm->getMergeDetails();
        foreach ($mergeDetails as $entityName => $ids) {
            foreach ($ids as $id) {
                $entity = $this->getEntityIfExists($entityName, $id);
                if ($entity !== null) {
                    $entity->setTransportManager($tm);
                }
            }
        }
    }

    /**
     * Get an entity if exists else return null
     *
     * @param string $entityName
     * @param int    $id
     *
     * @return object Entity | null
     * @throws \RuntimeException
     */
    protected function getEntityIfExists($entityName, $id)
    {
        $entityNameStartPos = strrpos($entityName, '\\');

        if ($entityNameStartPos === false) {
            throw new \RuntimeException('Unable to unmerge entity ' . $entityName);
        }

        $cleanEntityName = substr($entityName, $entityNameStartPos + 1);

        /** @var AbstractReadonlyRepository $repo */
        $repo = $this->getRepo($cleanEntityName);
        $repo->disableSoftDeleteable([$cleanEntityName]);
        try {
            $entity = $repo->fetchById($id);
        } catch (Exception\NotFoundException) {
            return null;
        }

        return $entity;
    }
}
