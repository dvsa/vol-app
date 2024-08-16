<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Service\PermitsReportService;
use Dvsa\Olcs\Transfer\Command\Permits\QueueReport as QueueReportCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class QueueReport extends AbstractCommandHandler
{
    use QueueAwareTrait;

    public const SUCCESS_MSG = 'Queued permit report of type %s';
    public const MISSING_REPORT_EXCEPTION = 'Requested report does not have an associated command';

    /**
     * Handle command
     *
     * @param QueueReportCmd|CommandInterface $command command
     *
     * @throws \Exception
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $availableReports = PermitsReportService::COMMAND_MAP;
        $reportId = $command->getId();

        if (!isset($availableReports[$reportId])) {
            throw new \Exception(self::MISSING_REPORT_EXCEPTION);
        }

        $this->result->merge(
            $this->handleSideEffect(
                $this->createQueue(null, Queue::TYPE_PERMIT_REPORT, $command->getArrayCopy())
            )
        );

        $this->result->addMessage(
            sprintf(self::SUCCESS_MSG, $reportId)
        );

        return $this->result;
    }
}
