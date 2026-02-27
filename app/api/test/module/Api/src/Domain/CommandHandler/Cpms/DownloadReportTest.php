<?php

declare(strict_types=1);

/**
 * Cpms Download Report Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cpms;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cpms\DownloadReport;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Command\Cpms\DownloadReport as Cmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 *  Cpms Download Report Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DownloadReportTest extends AbstractCommandHandlerTestCase
{
    public $mockCpmsService;
    protected $mockApi;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
        ];

        $this->sut = new DownloadReport();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $reference = 'OLCS-1234-FOO';
        $token = 'secrettoken';

        // expectations
        $this->mockCpmsService
            ->shouldReceive('downloadReport')
            ->once()
            ->with($reference, $token)
            ->andReturn("some,csv,data");

        $docResult = new Result();
        $docResult
            ->addId('document', 99)
            ->addId('identifier', 'path/to/document')
            ->addMessage('Document created')
            ->addMessage('File uploaded');
        $content = base64_encode("some,csv,data");
        $this->expectedSideEffect(
            UploadCmd::class,
            [
                'content'     => $content,
                'filename'    => 'foobar.csv',
                'category'    => Category::CATEGORY_LICENSING,
                'subCategory' => Category::DOC_SUB_CATEGORY_FINANCIAL_REPORTS,
                'isExternal'  => false,
                'user' => 1
            ],
            $docResult
        );

        // invoke
        $command = Cmd::create(
            [
                'reference' => $reference,
                'token' => $token,
                'filename' => 'foobar.csv',
                'user' => 1
            ]
        );
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals(
            ['Report downloaded', 'Document created','File uploaded'],
            $result->getMessages()
        );
        $this->assertEquals(
            ['document' => 99, 'identifier' => 'path/to/document'],
            $result->getIds()
        );
    }
}
