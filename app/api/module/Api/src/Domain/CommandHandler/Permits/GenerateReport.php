<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GenerateReport as GeneratePermitReportCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Service\PermitsReportService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class GenerateReport extends AbstractCommandHandler
{
    public const SUCCESS_MSG = 'Permit report of type %s generated';
    public const MISSING_REPORT_EXCEPTION = 'Requested report does not have an associated command';

    /**
     * Generate a permit report
     *
     * @param GeneratePermitReportCmd|CommandInterface $command command
     *
     * @throws \InvalidArgumentException
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $availableReports = PermitsReportService::COMMAND_MAP;
        $reportId = $command->getId();

        if (!isset($availableReports[$reportId])) {
            throw new \InvalidArgumentException(self::MISSING_REPORT_EXCEPTION);
        }

        $cmdClass = $availableReports[$reportId];
        $reportCmd = $cmdClass::create($command->getArrayCopy());

        $this->result->merge(
            $this->handleSideEffect($reportCmd)
        );

        $this->result->addMessage(sprintf(self::SUCCESS_MSG, $reportId));
        return $this->result;
    }
}
