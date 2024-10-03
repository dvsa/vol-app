<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Aws\S3\S3Client;
use Doctrine\DBAL\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\DataDvaNiExport;
use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * @covers \Dvsa\Olcs\Cli\Domain\CommandHandler\DataDvaNiExport
 */
class DataDvaNiExportTest extends AbstractCommandHandlerTestCase
{
    public $mockDbalResult;
    /**
     * @var DataDvaNiExport
     */
    protected $sut;

    private $tempDir;

    public function setUp(): void
    {
        //  mock repos
        $this->mockRepo('DataDvaNi', Repository\DataDvaNi::class);
        $this->mockRepo('TrafficArea', Repository\TrafficArea::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Category', Repository\Category::class);
        $this->mockRepo('SubCategory', Repository\SubCategory::class);
        $this->mockRepo('Licence', Repository\Licence::class);

        $this->mockDbalResult = m::mock(Result::class);

        $this->mockedSmServices['config'] = [
            'data-dva-ni-export' => [
                's3_uri' => 's3://testbucket/testprefix/',
            ],
        ];

        $this->mockS3client = m::mock(S3Client::class);
        $this->mockedSmServices[S3Client::class] = $this->mockS3client;

        $this->sut = new DataDvaNiExport();

        parent::setUp();
    }

    private function createTempDir()
    {
        $this->tempDir = sys_get_temp_dir() . '/phpunit_' . uniqid();
        mkdir($this->tempDir);
        return $this->tempDir;
    }

    private function cleanupRealTempDir()
    {
        if ($this->tempDir && is_dir($this->tempDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($this->tempDir);
        }
    }

    public function testInvalidReportException()
    {
        $cmd = Cmd::create(
            [
                'reportName' => 'INVALID',
                'path' => 'unit_Path',
            ]
        );

        //  expect
        $this->expectException(\Exception::class);

        //  call
        $this->sut->handleCommand($cmd);
    }

    public function testNiOperatorLicence()
    {
        $tempDir = $this->createTempDir();

        $cmd = Cmd::create(
            [
                'reportName' => DataDvaNiExport::NI_OPERATOR_LICENCE,
                'path' => $tempDir,
            ]
        );

        $row1 = [
            'LicenceNumber' => '123455',
            'LicenceType' => 'test_type',
        ];
        $row2 = [
            'LicenceNumber' => '123456',
            'LicenceType' => 'test_type',
        ];
        $row3 = [
            'LicenceNumber' => '123457',
            'LicenceType' => 'test_type',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row3);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['DataDvaNi']
            ->shouldReceive('fetchNiOperatorLicences')
            ->once()
            ->andReturn($this->mockDbalResult);

        $this->mockS3client->shouldReceive('putObject')->once()->andReturn([]);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $date = new DateTime('now');

        $expectCsvFile =  '/tmp/NiGvLicences-' . $date->format(DataDvaNiExport::FILE_DATETIME_FORMAT) . '.csv';
        $expectTgzFile =  'dvaoplic-' . $date->format(DataDvaNiExport::FILE_DATETIME_FORMAT) . '.tar.gz';

        $expectMsg =
            'Fetching data from DB for NI Operator Licences' .
            'Creating CSV file: '  . $expectCsvFile .
            'Uploaded file to S3: ' . $expectTgzFile;

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }
}
