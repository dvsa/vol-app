<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CompaniesHouseVsOlcsDiffsExportCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return CompaniesHouseVsOlcsDiffsExportCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:companies-house-vs-olcs-diffs-export';
    }

    protected function getCommandDTOs(): array
    {
        $dtoData = [];
        $dtoData['path'] = $this->additionalArguments['--path'];
        return [
            CompaniesHouseVsOlcsDiffsExport::create($dtoData),
        ];
    }

    protected $additionalArguments = ['--path' => 'test/path'];

    public function testExecuteWithoutPath(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
