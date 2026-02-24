<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Command\Batch\DataGovUkExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DataGovUkExportCommandTest extends AbstractBatchCommandCases
{
    protected $additionalArguments = [
        '--report-name' => 'govReport',
    ];

    protected function getCommandClass(): string
    {
        return DataGovUkExportCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:data-gov-uk-export';
    }

    protected function getCommandDTOs(): array
    {
        return [
            DataGovUkExport::create([
                'reportName' => $this->additionalArguments['--report-name']
            ])
        ];
    }
}
