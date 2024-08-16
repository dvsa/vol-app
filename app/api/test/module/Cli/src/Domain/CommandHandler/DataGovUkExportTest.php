<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPsvOperatorListReport;
use Dvsa\Olcs\Api\Domain\Command\Email\SendInternationalGoods as SendInternationalGoodsCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\DataGovUkExport;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Email\Service\Email;
use org\bovigo\vfs\vfsStream;
use Mockery as m;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * @covers \Dvsa\Olcs\Cli\Domain\CommandHandler\DataGovUkExport
 */
class DataGovUkExportTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var DataGovUkExport
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
    private $mockDbalResult;

    public function setUp(): void
    {
        //  mock repos
        $this->mockRepo('DataGovUk', Repository\DataGovUk::class);
        $this->mockRepo('TrafficArea', Repository\TrafficArea::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Category', Repository\Category::class);
        $this->mockRepo('SubCategory', Repository\SubCategory::class);
        $this->mockRepo('Licence', Repository\Licence::class);

        $this->mockDbalResult = m::mock(\Doctrine\DBAL\Result::class);

        //  mock config
        $this->mockedSmServices['config'] = [
            'data-gov-uk-export' => [
                'path' => 'unit_CfgPath',
            ],
        ];

        /** @var Email $mockEmailService */
        $mockEmailService = m::mock(Email::class);

        /** @var ContentStoreFileUploader $mockFileUploader */
        $mockFileUploader = m::mock(ContentStoreFileUploader::class);

        /** @var NamingService $mockDocumentNaming */
        $mockDocumentNaming = m::mock(NamingService::class);

        $this->mockedSmServices['EmailService'] = $mockEmailService;
        $this->mockedSmServices['FileUploader'] = $mockFileUploader;
        $this->mockedSmServices['DocumentNamingService'] = $mockDocumentNaming;

        $this->sut = new DataGovUkExport();

        parent::setUp();

        $this->vfsStream = vfsStream::setup('root');
        $this->tmpPath = $this->vfsStream->url() . '/unit';
    }

    protected function initReferences()
    {
        $this->categoryReferences = [
            Category::CATEGORY_REPORT => m::mock(Category::class),
        ];

        $this->subCategoryReferences = [
            SubCategory::REPORT_SUB_CATEGORY_PSV  => m::mock(SubCategory::class),
        ];

        parent::initReferences();
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
        $this->expectExceptionMessage(DataGovUkExport::ERR_INVALID_REPORT);

        //  call
        $this->sut->handleCommand($cmd);
    }

    public function testInternationalGoods()
    {
        $fileName = 'international_goods';

        $vfsFile = new vfsStreamFile($fileName, 0777);
        $tmpFolder = new vfsStreamDirectory('unit');
        $tmpFolder->addChild($vfsFile);

        $directory = new vfsStreamDirectory('root');
        $directory->addChild($tmpFolder);

        $row1 = [
            'Licence number' => 'LicNo1',
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];

        $row2 = [
            'Licence number' => 'LicNo2',
            'col1' => 'val21',
            'col2' => 'val22',
        ];

        $row3 = [
            'Licence number' => 'LicNo3',
            'col1' => 'val31',
            'col2' => 'val32',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row3);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['Licence']
            ->shouldReceive('internationalGoodsReport')
            ->once()
            ->andReturn($this->mockDbalResult);

        // Create document in database
        $documentData['description'] = 'International goods list ' . date('d/m/Y');
        $documentData['filename'] = 'international-goods-list.csv';
        $documentData['user'] = IdentityProviderInterface::SYSTEM_USER;
        $documentData['category'] = Category::CATEGORY_REPORT;
        $documentData['subCategory'] = SubCategory::REPORT_SUB_CATEGORY_GV;

        $this->expectedSideEffect(
            UploadCmd::class,
            $documentData,
            (new Result())->addMessage('CreateDocument')->addId('document', 666)
        );

        // Send email
        $this->expectedEmailQueueSideEffect(
            SendInternationalGoodsCmd::class,
            ['id' => 666],
            666,
            new Result()
        );

        //  call & check
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::INTERNATIONAL_GOODS,
                'path' => $this->tmpPath,
            ]
        );

        $actual = $this->sut->handleCommand($cmd);

        $date = new DateTime('now');
        $expectedFile = $this->tmpPath . '/' . $fileName . '_' .
            $date->format(DataGovUkExport::FILE_DATETIME_FORMAT) . '.csv';

        $expectMsg =
            'Fetching data for international goods list' .
            'create csv file: ' . $expectedFile;

        $this->assertSame(
            '"Licence number",col1,col2' . "\n" .
            'LicNo1,val11,"v""\'-/\\,"' . "\n" .
            'LicNo2,val21,val22' . "\n" .
            'LicNo3,val31,val32' . "\n" .
            '',
            file_get_contents($expectedFile)
        );

        $this->assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testPsvOperatorListOk()
    {
        $fileName = 'psv-operator-list.csv';

        $vfsFile = new vfsStreamFile($fileName, 0777);
        $tmpFolder = new vfsStreamDirectory('unit');
        $tmpFolder->addChild($vfsFile);

        $directory = new vfsStreamDirectory('root');
        $directory->addChild($tmpFolder);

        $row1 = [
            'Licence number' => 'areaName1',
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];

        $row2 = [
            'Licence number' => 'areaName2',
            'col1' => 'val21',
            'col2' => 'val22',
        ];

        $row3 = [
            'Licence number' => 'areaName1',
            'col1' => 'val31',
            'col2' => 'val32',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row3);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['DataGovUk']
            ->shouldReceive('fetchPsvOperatorList')
            ->once()
            ->andReturn($this->mockDbalResult);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->with(
                'PsvOperatorList',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                $this->subCategoryReferences[SubCategory::REPORT_SUB_CATEGORY_PSV]
            )
            ->andReturn($this->tmpPath . '/' . $fileName);

        /** @var File|m\mock $contentStoreFile */
        $contentStoreFile = m::mock(File::class);
        $contentStoreFile->shouldReceive('getContent')
            ->andReturn(
                '"Licence number",col1,col2' . PHP_EOL .
                'areaName1,val11,"v""\'-/\\,"' . PHP_EOL .
                'areaName2,val21,val22' . PHP_EOL .
                'areaName1,val31,val32' . PHP_EOL .
                ''
            );
        $contentStoreFile->shouldReceive('getResource')
            ->andReturn($this->tmpPath . '/' . $fileName);
        $contentStoreFile->shouldReceive('getMimeType')
            ->andReturn('text/csv');
        $contentStoreFile->shouldReceive('getSize')
            ->andReturn(98);
        $contentStoreFile->shouldReceive('getIdentifier')
            ->andReturn($fileName);

        // Upload file
        $this->mockedSmServices['FileUploader']
            ->shouldAllowMockingMethod('upload');

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')->andReturn($contentStoreFile);

        // Create document in database
        $documentData['description'] = 'PSV Operator list';
        $documentData['filename'] = $fileName;
        $documentData['user'] = IdentityProviderInterface::SYSTEM_USER;
        $documentData['category'] = Category::CATEGORY_REPORT;
        $documentData['subCategory'] = SubCategory::REPORT_SUB_CATEGORY_PSV;

        $this->expectedSideEffect(
            UploadCmd::class,
            $documentData,
            (new Result())->addMessage('CreateDocument')->addId('document', 1)
        );

        // Send email
        $this->expectedEmailQueueSideEffect(
            SendPsvOperatorListReport::class,
            ['id' => 1],
            1,
            new Result()
        );

        //  call & check
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::PSV_OPERATOR_LIST,
                'path' => $this->tmpPath,
            ]
        );

        $actual = $this->sut->handleCommand($cmd);

        $expectFile = $this->tmpPath . '/' . $fileName;

        $expectMsg =
            'Fetching data from DB for PSV Operators' .
            'create csv file content';

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testOperatorLicenceOk()
    {
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::OPERATOR_LICENCE,
                'path' => $this->tmpPath,
            ]
        );

        //  mock repository
        $this->mockTrafficAreaRepo();

        $row1 = [
            'GeographicRegion' => 'areaName1',
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];
        $row2 = [
            'GeographicRegion' => 'areaName2',
            'col1' => 'val21',
            'col2' => 'val22',
        ];
        $row3 = [
            'GeographicRegion' => 'areaName1',
            'col1' => 'val31',
            'col2' => 'val32',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row3);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['DataGovUk']
            ->shouldReceive('fetchOperatorLicences')
            ->once()
            ->andReturn($this->mockDbalResult);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 = $this->tmpPath . '/OLBSLicenceReport_areaName1.csv';
        $expectFile2 = $this->tmpPath . '/OLBSLicenceReport_areaName2.csv';

        $expectMsg =
            'Fetching data from DB for Operator Licences' .
            'create csv file: ' . $expectFile1 .
            'create csv file: ' . $expectFile2;

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );

        static::assertSame(
            'GeographicRegion,col1,col2' . "\n" .
            'areaName1,val11,"v""\'-/\,"' . "\n" .
            'areaName1,val31,val32' . "\n",
            file_get_contents($expectFile1)
        );
        static::assertSame(
            'GeographicRegion,col1,col2' . "\n" . 'areaName2,val21,val22' . "\n",
            file_get_contents($expectFile2)
        );
    }

    public function testBugRegOnlyOk()
    {
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::BUS_REGISTERED_ONLY,
                'path' => $this->tmpPath,
            ]
        );

        //  mock repository
        $this->mockTrafficAreaRepo();

        $row1 = [
            'Current Traffic Area' => 'areaId1',
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];
        $row2 = [
            'Current Traffic Area' => 'areaId1',
            'col1' => 'val21',
            'col2' => 'val22',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row2);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['DataGovUk']
            ->shouldReceive('fetchBusRegisteredOnly')
            ->once()
            ->andReturn($this->mockDbalResult);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 = $this->tmpPath . '/Bus_RegisteredOnly_areaId1.csv';

        $expectMsg =
            'Fetching data from DB for Bus Registered Only' .
            'create csv file: ' . $expectFile1;

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );

        static::assertSame(
            '"Current Traffic Area",col1,col2' . "\n" .
            'areaId1,val11,"v""\'-/\,"' . "\n" .
            'areaId1,val21,val22' . "\n",
            file_get_contents($expectFile1)
        );
    }

    public function testBugVariationOk()
    {
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::BUS_VARIATION,
                'path' => $this->tmpPath,
            ]
        );

        //  mock repository
        $this->mockTrafficAreaRepo();

        $row1 = [
            'Current Traffic Area' => 'areaId1',
            'col1' => 'val11',
            'col2' => 'v"\'-/\,',
        ];

        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturn($row1);
        $this->mockDbalResult->expects('fetchAssociative')->withNoArgs()->andReturnFalse();

        $this->repoMap['DataGovUk']
            ->shouldReceive('fetchBusVariation')
            ->once()
            ->andReturn($this->mockDbalResult);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 = $this->tmpPath . '/Bus_Variation_areaId1.csv';

        $expectMsg =
            'Fetching data from DB for Bus Variation' .
            'create csv file: ' . $expectFile1;

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );

        static::assertSame(
            '"Current Traffic Area",col1,col2' . "\n" .
            'areaId1,val11,"v""\'-/\,"' . "\n",
            file_get_contents($expectFile1)
        );
    }

    public function testTrafficAreaNotFound()
    {
        $this->repoMap['TrafficArea']
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn([]);

        //  expect
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(DataGovUkExport::ERR_NO_TRAFFIC_AREAS);

        //  call
        $this->sut->handleCommand(
            Cmd::create(['reportName' => DataGovUkExport::BUS_REGISTERED_ONLY])
        );
    }

    private function mockTrafficAreaRepo()
    {
        $this->repoMap['TrafficArea']
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn(
                [
                    m::mock(TrafficArea::class)
                        ->shouldReceive('getId')->atMost()->andReturn('areaId1')
                        ->shouldReceive('getName')->andReturn('areaName1')
                        ->getMock(),
                    m::mock(TrafficArea::class)
                        ->shouldReceive('getId')->zeroOrMoreTimes()->andReturn('areaId2')
                        ->shouldReceive('getName')->andReturn('areaName2')
                        ->getMock(),
                ]
            );
    }
}
