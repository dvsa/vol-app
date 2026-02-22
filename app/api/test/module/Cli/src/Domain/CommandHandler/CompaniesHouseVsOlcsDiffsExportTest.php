<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Dvsa\Olcs\Cli\Domain\CommandHandler\CompaniesHouseVsOlcsDiffsExport
 */
class CompaniesHouseVsOlcsDiffsExportTest extends AbstractCommandHandlerTestCase
{
    /** @var CompaniesHouseVsOlcsDiffsExport */
    protected $sut;

    /** @var  string */
    private $tmpPath;

    public function setUp(): void
    {
        $this->sut = new CompaniesHouseVsOlcsDiffsExport();

        //  mock repos
        $this->mockRepo('CompanyHouseVsOlcsDiffs', Repository\CompaniesHouseVsOlcsDiffs::class);

        //  mock config
        $this->tmpPath = vfsStream::setup('root')->url() . '/unit';

        $this->mockedSmServices['config'] = [
            'ch-vs-olcs-export' => [
                'path' => 'unit_CfgPath',
            ],
        ];

        parent::setUp();
    }

    public function testMakeCsvsFromResult(): void
    {
        $cmd = Cmd::create(
            [
                'path' => $this->tmpPath,
            ]
        );

        //  mock repository
        $this->mockRepoMethod('fetchOfficerDiffs');
        $this->mockRepoMethod('fetchAddressDiffs');
        $this->mockRepoMethod('fetchNameDiffs');
        $this->mockRepoMethod('fetchWithNotActiveStatus');

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        //  check file content
        $this->checkFileContent('CompanyOfficerDiffs');
        $this->checkFileContent('CompanyAddressDiffs');
        $this->checkFileContent('CompanyNameDiffs');
        $this->checkFileContent('CompanyNotActive');

        //  check messages
        $expectMsg =
            'Fetching data from DB for Company house and Organisation Name differences' .
            'create csv file: ' . $this->tmpPath . '/CompanyNameDiffs.csv' .
            'Fetching data from DB where Organisation not active in Company house' .
            'create csv file: ' . $this->tmpPath . '/CompanyNotActive.csv' .
            'Fetching data from DB for Company house and Organisation Address differences' .
            'create csv file: ' . $this->tmpPath . '/CompanyAddressDiffs.csv' .
            'Fetching data from DB for Company house and Organisation Officers differences' .
            'create csv file: ' . $this->tmpPath . '/CompanyOfficerDiffs.csv';

        static::assertEquals($expectMsg, implode('', $actual->toArray()['messages']));
    }

    private function mockRepoMethod(mixed $repoMethod): void
    {
        $row1 = [
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];
        $row2 = [
            'col1' => 'val21',
            'col2' => 'val22',
        ];

        $mockDbalResult = m::mock(Result::class);
        $mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['CompanyHouseVsOlcsDiffs']
            ->shouldReceive($repoMethod)
            ->once()
            ->andReturn($mockDbalResult);
    }

    private function checkFileContent(mixed $fileName): void
    {
        static::assertSame(
            'col1,col2' . "\n" .
            'val11,"v""\'-/\,"' . "\n" .
            'val21,val22' . "\n",
            file_get_contents($this->tmpPath . '/' . $fileName . '.csv')
        );
    }
}
