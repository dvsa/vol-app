<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Accept a granted/awarded IRHP application
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class AcceptIrhpPermits extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();

        $irhpApplicationRepo = $this->getRepo();

        $irhpApplication = $irhpApplicationRepo->fetchById($irhpApplicationId);
        $this->result->addId('irhpApplication', $irhpApplicationId);

        try {
            $irhpApplication->proceedToIssuing(
                $this->refData(IrhpInterface::STATUS_ISSUING)
            );
        } catch (ForbiddenException) {
            $this->result->addMessage('Unable to issue permit for application');
            return $this->result;
        }

        $irhpApplicationRepo->save($irhpApplication);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createQueue($irhpApplicationId, Queue::TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE, [])
            )
        );

        $this->result->addMessage('Queued allocation of permits');

        return $this->result;
    }
}
