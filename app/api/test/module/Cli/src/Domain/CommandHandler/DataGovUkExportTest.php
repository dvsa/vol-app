<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Aws\S3\S3Client;
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
use Psr\Container\ContainerInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Domain\CommandHandler\DataGovUkExport::class)]
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
     * @var  m\MockInterface
     */
    private $mockDbalResult;

    protected $mockS3client;


    public function setUp(): void
    {
        $this->tmpPath = sys_get_temp_dir();

        //  mock repos
        $this->mockRepo('DataGovUk', Repository\DataGovUk::class);
        $this->mockRepo('TrafficArea', Repository\TrafficArea::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Category', Repository\Category::class);
        $this->mockRepo('SubCategory', Repository\SubCategory::class);
        $this->mockRepo('Licence', Repository\Licence::class);

        $this->mockDbalResult = m::mock(\Doctrine\DBAL\Result::class);

        $this->mockedSmServices['config'] = [
            'data-gov-uk-export' => [
                's3_uri' => 's3://testbucket/testprefix/',
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

        $this->mockS3client = m::mock(S3Client::class);
        $this->mockedSmServices[S3Client::class] = $this->mockS3client;
        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with(S3Client::class)->andReturn($this->mockS3client);

        $this->sut = new DataGovUkExport();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->categoryReferences = [
            Category::CATEGORY_REPORT => m::mock(Category::class),
        ];

        $this->subCategoryReferences = [
            SubCategory::REPORT_SUB_CATEGORY_PSV  => m::mock(SubCategory::class),
        ];

        parent::initReferences();
    }

    public function testInvalidReportException(): void
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

    public function testInternationalGoods(): void
    {
        $fileName = 'international_goods';

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

        // Call & check
        $cmd = Cmd::create(
            [
                'reportName' => DataGovUkExport::INTERNATIONAL_GOODS,
                'path' => $this->tmpPath,
            ]
        );

        $this->mockS3client->shouldAllowMockingMethod('putObject');
        $this->mockS3client->shouldReceive('putObject')
            ->once()
            ->andReturn([]);

        $actual = $this->sut->handleCommand($cmd);

        $date = new DateTime('now');

        $expectedFileName = $fileName . '_' .
            $date->format(DataGovUkExport::FILE_DATETIME_FORMAT) . '.csv';
        $expectedFilePath = $this->tmpPath . '/' . $expectedFileName;

        $expectMsg =
            'Fetching data for international goods list' .
            'Creating CSV file: ' . $expectedFilePath . 'Uploaded file to S3: ' . $expectedFileName;

        $this->assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testPsvOperatorListOk(): void
    {
        $fileName = 'psv-operator-list.csv';

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

        $this->mockS3client->shouldAllowMockingMethod('putObject');
        $this->mockS3client->shouldReceive('putObject')
            ->once()
            ->andReturn([]);

        $actual = $this->sut->handleCommand($cmd);

        $expectMsg =
            'Fetching data from DB for PSV Operators' .
            'create csv file contentUploaded file to S3: psv_operator_list_';

        $actualMsg = implode('', $actual->toArray()['messages']);

        static::assertStringStartsWith($expectMsg, $actualMsg);
    }

    public function testOperatorLicenceOk(): void
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

        $this->mockS3client->shouldAllowMockingMethod('putObject');
        $this->mockS3client->shouldReceive('putObject')
            ->twice()
            ->andReturn([]);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 = $this->tmpPath . '/OLBSLicenceReport_areaName1.csv';
        $expectFile2 = $this->tmpPath . '/OLBSLicenceReport_areaName2.csv';

        $expectMsg =
            'Fetching data from DB for Operator Licences' .
            'Creating CSV file: ' . $expectFile1 .
            'Creating CSV file: ' . $expectFile2 .
            'Uploaded file to S3: OLBSLicenceReport_areaName1.csvUploaded file to S3: OLBSLicenceReport_areaName2.csv';

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testBugRegOnlyOk(): void
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

        $this->mockS3client->shouldReceive('putObject')
            ->once()
            ->andReturn([]);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 =  $this->tmpPath . '/Bus_RegisteredOnly_areaId1.csv';

        $expectMsg =
            'Fetching data from DB for Bus Registered Only' .
            'Creating CSV file: ' . $expectFile1 . 'Uploaded file to S3: Bus_RegisteredOnly_areaId1.csv';

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testBugVariationOk(): void
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

        $this->mockS3client->shouldReceive('putObject')
            ->once()
            ->andReturn([]);

        //  call & check
        $actual = $this->sut->handleCommand($cmd);

        $expectFile1 = $this->tmpPath . '/Bus_Variation_areaId1.csv';

        $expectMsg =
            'Fetching data from DB for Bus Variation' .
            'Creating CSV file: ' . $expectFile1 . 'Uploaded file to S3: Bus_Variation_areaId1.csv';

        static::assertEquals(
            $expectMsg,
            implode('', $actual->toArray()['messages'])
        );
    }

    public function testTrafficAreaNotFound(): void
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

    private function mockTrafficAreaRepo(): void
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
