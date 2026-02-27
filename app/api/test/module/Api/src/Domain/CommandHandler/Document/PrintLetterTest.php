<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\PrintLetter;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service as ApiSrv;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Document\PrintLetter::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class PrintLetterTest extends AbstractCommandHandlerTestCase
{
    public const LIC_ID = 8001;

    public const DOC_ID = 7001;
    public const DOC_DESC = 'unit test doc description';

    /** @var  PrintLetter */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockDocRepo;
    /** @var  m\MockInterface | Entity\Doc\Document */
    private $mockDocE;
    /** @var  m\MockInterface */
    private $mockPrintSrv;

    public function setUp(): void
    {
        /** @var Entity\Licence\Licence $mockLicE */
        $mockLicE = m::mock(Entity\Licence\Licence::class)->makePartial();
        $mockLicE->setId(self::LIC_ID);

        $this->mockDocE = m::mock(Entity\Doc\Document::class)->makePartial();
        $this->mockDocE
            ->setId(self::DOC_ID)
            ->setDescription(self::DOC_DESC);
        $this->mockDocE->shouldReceive('getRelatedLicence')->andReturn($mockLicE);

        $this->mockDocRepo = $this->mockRepo('Document', Repository\Document::class);

        $this->mockPrintSrv = m::mock(ApiSrv\Document\PrintLetter::class);

        $this->mockedSmServices[ApiSrv\Document\PrintLetter::class] = $this->mockPrintSrv;

        $this->sut = new PrintLetter();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $method, array $canDo, array $expect): void
    {
        $data = [
            'id' => self::DOC_ID,
            'method' => $method,
        ];
        $command = TransferCmd\Document\PrintLetter::create($data);

        $this->mockDocRepo->shouldReceive('fetchUsingId')->with($command)->andReturn($this->mockDocE);

        $this->mockPrintSrv
            ->shouldReceive('canPrint')->with($this->mockDocE)->andReturn($canDo['print'])
            ->shouldReceive('canEmail')->with($this->mockDocE, false)->andReturn($canDo['email']);

        foreach ($expect['assert'] as $assert) {
            switch ($assert) {
                case 'sendEmail':
                    $this->assertSendEmail();
                    break;
                case 'attemptPrint':
                    $this->assertAttemptPrint();
                    break;
                case 'createTranslationTask':
                    $this->assertCreateTranslationTask();
                    break;
                default:
            }
        }

        $actual = $this->sut->handleCommand($command);

        static::assertEquals($expect['result'], $actual->getMessages());
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            'method:Email;canEmail&Print;' => [
                'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
                'canDo' => [
                    'email' => true,
                    'print' => true,
                ],
                'expect' => [
                    'result' => ['SendEmail'],
                    'assert' => [
                        'sendEmail',
                    ],
                ],
            ],
            'method:Print;canEmail&Print;' => [
                'method' => TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST,
                'canDo' => [
                    'email' => true,
                    'print' => true,
                ],
                'expect' => [
                    'result' => ['AttemptPrint'],
                    'assert' => [
                        'attemptPrint',
                    ],
                ],
            ],
            'method:Email;canNotEmail&Print;' => [
                'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
                'canDo' => [
                    'email' => false,
                    'print' => true,
                ],
                'expect' => [
                    'result' => [],
                    'assert' => [],
                ],
            ],
            'method:Print;canEmail&NotPrint;addTranslation' => [
                'method' => TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST,
                'canDo' => [
                    'email' => true,
                    'print' => false,
                ],
                'expect' => [
                    'result' => ['CreateTranslationTask'],
                    'assert' => [
                        'createTranslationTask',
                    ],
                ],
            ],
            'method:Email;canEmail&NotPrint;addTranslation' => [
                'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
                'canDo' => [
                    'email' => true,
                    'print' => false,
                ],
                'expect' => [
                    'result' => [
                        'CreateTranslationTask',
                        'SendEmail',
                    ],
                    'assert' => [
                        'createTranslationTask',
                        'sendEmail',
                    ],
                ],
            ],
        ];
    }

    private function assertAttemptPrint(): void
    {
        $data = [
            'documentId' => self::DOC_ID,
            'jobName' => self::DOC_DESC,
        ];

        $result = new Result();
        $result->addMessage('AttemptPrint');

        $this->expectedSideEffect(DomainCmd\PrintScheduler\Enqueue::class, $data, $result);
    }

    private function assertSendEmail(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'document' => self::DOC_ID,
            'type' => DomainCmd\Email\CreateCorrespondenceRecord::TYPE_STANDARD,
        ];

        $result = new Result();
        $result->addMessage('SendEmail');

        $this->expectedSideEffect(DomainCmd\Email\CreateCorrespondenceRecord::class, $data, $result);
    }

    private function assertCreateTranslationTask(): void
    {
        $data = [
            'description' => self::DOC_DESC,
            'licence' => self::LIC_ID,
        ];

        $result = new Result();
        $result->addMessage('CreateTranslationTask');

        $this->expectedSideEffect(DomainCmd\Task\CreateTranslateToWelshTask::class, $data, $result);
    }

    public function testNulls(): void
    {
        $mockDocE = m::mock(Entity\Doc\Document::class)->makePartial();
        $mockDocE->shouldReceive('getRelatedLicence')->andReturnNull();

        $this->mockDocRepo->shouldReceive('fetchUsingId')->andReturn($mockDocE);

        $this->mockPrintSrv
            ->shouldReceive('canPrint')->andReturn(false)
            ->shouldReceive('canEmail')->andReturn(true);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->never()
            ->with(m::type(DomainCmd\Task\CreateTranslateToWelshTask::class), false)
            ->shouldReceive('handleCommand')
            ->never()
            ->with(m::type(DomainCmd\Email\CreateCorrespondenceRecord::class), false);

        $this->sut->handleCommand(
            TransferCmd\Document\PrintLetter::create(
                [
                    'id' => self::DOC_ID,
                    'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
                ]
            )
        );
    }
}
