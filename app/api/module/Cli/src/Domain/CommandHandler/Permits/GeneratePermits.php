<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Permits\GeneratePermitDocuments;
use Dvsa\Olcs\Api\Domain\Command\Permits\ProceedToStatus;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Generate permits
 */
final class GeneratePermits extends AbstractCommandHandler
{
    private $transMngr;

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();

        if (empty($ids)) {
            throw new ValidationException(['Empty list of permits provided.']);
        }

        $userId = $command->getUser();

        if (empty($userId)) {
            throw new ValidationException(['No user provided.']);
        }

        try {
            // begin db transaction
            $this->transMngr->beginTransaction();

            // proceed to printing
            $this->proceedToStatus($ids, IrhpPermitEntity::STATUS_PRINTING);

            // generate documents
            $docs = $this->generateDocuments($ids);

            // print permits
            $this->printPermits($docs['permits'], $userId);

            if (!empty($docs['letters'])) {
                // print covering letters
                $this->printLetters($docs['letters'], $userId);
            }

            // proceed to printed
            $this->proceedToStatus($ids, IrhpPermitEntity::STATUS_PRINTED);

            // commit db transaction
            $this->transMngr->commit();

            $this->result->addMessage('Permits generated');
        } catch (\Exception $exc) {
            // rollback db transaction
            $this->transMngr->rollback();

            // begin db transaction
            $this->transMngr->beginTransaction();
            // proceed to error
            $this->proceedToStatus($ids, IrhpPermitEntity::STATUS_ERROR);
            // commit db transaction
            $this->transMngr->commit();

            throw new RuntimeException(
                'Permits generation failed with error: ' . $exc->getMessage(),
                $exc->getCode(),
                $exc
            );
        }

        return $this->result;
    }

    /**
     * Proceed to given status
     *
     * @param array  $ids    List of permits
     * @param string $status Status to proceed to
     *
     * @return void
     */
    private function proceedToStatus(array $ids, $status)
    {
        // update status of permits
        $this->result->merge(
            $this->handleSideEffect(
                ProceedToStatus::create(
                    [
                        'ids' => $ids,
                        'status' => $status,
                    ]
                )
            )
        );
    }

    /**
     * Generate documents for permits and covering letters
     *
     * @param array $ids List of permits
     *
     * @return array
     * @throws RuntimeException
     */
    private function generateDocuments(array $ids)
    {
        $result = $this->handleSideEffect(GeneratePermitDocuments::create(['ids' => $ids]));

        $this->result->merge($result);

        $docs = $result->getIds();

        if (empty($docs)) {
            throw new RuntimeException('No documents generated.');
        }

        if (empty($docs['permit'])) {
            throw new RuntimeException('No permits generated.');
        }

        // cast to array to make sure it's always a list of items (including one item list)
        $permits = (array)$docs['permit'];
        $letters = !empty($docs['coveringLetter']) ? (array)$docs['coveringLetter'] : [];

        return [
            'permits' => $permits,
            'letters' => $letters,
        ];
    }

    /**
     * Print permits
     *
     * @param array $docs   List of permits to be printed
     * @param int   $userId Id of user who scheduled printing
     *
     * @return void
     */
    private function printPermits(array $docs, $userId)
    {
        $printQueue = EnqueueFileCommand::create(
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => $docs,
                'jobName' => 'Permits',
                'user' => $userId,
            ]
        );
        $this->result->merge($this->handleSideEffect($printQueue));
    }

    /**
     * Print covering letters
     *
     * @param array $docs   List of letters to be printed
     * @param array $userId Id of user who scheduled printing
     *
     * @return void
     */
    private function printLetters(array $docs, $userId)
    {
        foreach ($docs as $docId) {
            $printQueue = EnqueueFileCommand::create(
                [
                    'documentId' => $docId,
                    'jobName' => 'Permit covering letter',
                    'user' => $userId,
                ]
            );
            $this->result->merge($this->handleSideEffect($printQueue));
        }
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->transMngr = $container->get('TransactionManager');
        return parent::__invoke($container, $requestedName, $options);
    }
}
