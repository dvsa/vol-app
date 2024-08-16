<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Doctrine\DBAL\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\DataDvaNiExport;
use Dvsa\Olcs\Api\Domain\Repository;
use org\bovigo\vfs\vfsStream;
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

    /**
     * @var  string
     */
    private $tmpPath;

    /**
     * @var vfsStream
     */
    private $vfsStream;

    /**
     * @var  m\MockInterface
     */
    private $mockStmt;

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

        //  mock config
        $this->mockedSmServices['config'] = [
            'data-dva-ni-export' => [
                'path' => 'unit_CfgPath',
            ],
        ];

        $this->sut = new DataDvaNiExport();

        parent::setUp();

        $this->vfsStream = vfsStream::setup('root');
        $this->tmpPath = $this->vfsStream->url() . '/unit';
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
        $cmd = Cmd::create(
            [
                'reportName' => DataDvaNiExport::NI_OPERATOR_LICENCE,
                'path' => $this->tmpPath,
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

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $date = new DateTime('now');

        $expectFile = $this->tmpPath . '/NiGvLicences-' . $date->format(DataDvaNiExport::FILE_DATETIME_FORMAT) . '.csv';

        $expectMsg =
            'Fetching data from DB for NI Operator Licences' .
            'create csv file: ' . $expectFile;

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );

        static::assertSame(
            'LicenceNumber,LicenceType' . "\n" .
            '123455,test_type' . "\n" .
            '123456,test_type' . "\n" .
            '123457,test_type' . "\n",
            file_get_contents($expectFile)
        );
    }
}
