<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

class CompaniesHouseVsOlcsDiffsExportCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return CompaniesHouseVsOlcsDiffsExportCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:companies-house-vs-olcs-diffs-export';
    }

    protected function getCommandDTOs()
    {
        $dtoData = [];
        $dtoData['path'] = $this->additionalArguments['--path'];
        return [
            CompaniesHouseVsOlcsDiffsExport::create($dtoData),
        ];
    }

    protected $additionalArguments = ['--path' => 'test/path'];

    public function testExecuteWithoutPath()
    {
        $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
