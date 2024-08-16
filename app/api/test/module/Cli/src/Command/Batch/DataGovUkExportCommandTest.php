<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Command\Batch\DataGovUkExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport;

class DataGovUkExportCommandTest extends AbstractBatchCommandCases
{
    protected $additionalArguments = [
        '--report-name' => 'govReport',
        '--path' => '/gov/path/to/save',
    ];

    protected function getCommandClass()
    {
        return DataGovUkExportCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:data-gov-uk-export';
    }

    protected function getCommandDTOs()
    {
        return [
            DataGovUkExport::create([
                'reportName' => $this->additionalArguments['--report-name'],
                'path' => $this->additionalArguments['--path']
            ])
        ];
    }
}
