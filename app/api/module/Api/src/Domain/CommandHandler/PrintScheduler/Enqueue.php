<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrintScheduler;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * Queue a print job
 *
 */
final class Enqueue extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\AuthAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\AuthAwareTrait;

    protected $repoServiceName = 'Document';

    protected $extraRepos = ['User'];

    /**
     * Handle Command
     *
     * @param CommandInterface $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws BadRequestException
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue */

        $documents = is_numeric($command->getDocumentId()) ? [$command->getDocumentId()] : $command->getDocuments();

        if (empty($documents)) {
            throw new ValidationException(
                ['List of documents to be printed must be provided.']
            );
        }

        $user = $command->getUser() ? $this->getRepo('User')->fetchById($command->getUser()) : $this->getCurrentUser();
        // if the user is in a team check the user has a team printer assigned
        if ($user->getTeam() && $user->getTeam()->getTeamPrinters()->isEmpty()) {
            throw new BadRequestException(
                'Failed to generate document as there are no printer settings for the current user'
            );
        }

        $dtoData = [
            'type' => $command->getType() ? : Queue::TYPE_PRINT,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode(
                array_filter(
                    [
                        'documents' => $documents,
                        'userId' => $user->getId(),
                        'jobName' => $command->getJobName(),
                        'copies' => $command->getCopies(),
                    ]
                )
            ),
        ];

        if ($command->getIsDiscPrinting()) {
            // if disc printing then use bespoke queue print type
            $dtoData['type'] = Queue::TYPE_DISC_PRINTING_PRINT;
        }

        $this->handleSideEffect(\Dvsa\Olcs\Api\Domain\Command\Queue\Create::create($dtoData));

        $this->result->addMessage(
            sprintf(
                '%s queued for print (document ids: %s)',
                $command->getJobName(),
                implode(', ', $documents)
            )
        );
        return $this->result;
    }
}
