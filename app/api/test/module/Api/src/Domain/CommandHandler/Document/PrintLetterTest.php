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
final class PrintLetterTest extends AbstractCommandHandlerTestCase
{
    public const int LIC_ID = 8001;

    public const int DOC_ID = 7001;
    public const string DOC_DESC = 'unit test doc description';

    /** @var  PrintLetter */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockDocRepo;
    /** @var  m\MockInterface | Entity\Doc\Document */
    private $mockDocE;
    /** @var  m\MockInterface */
    private $mockPrintSrv;
    /** @var  m\MockInterface */
    private $mockLetterInstanceRepo;

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
        $this->mockLetterInstanceRepo = $this->mockRepo('LetterInstance', Repository\LetterInstance::class);
        // most documents weren't built in the letter builder
        $this->mockLetterInstanceRepo->shouldReceive('fetchByDocument')->byDefault()->andReturnNull();

        $this->mockPrintSrv = m::mock(ApiSrv\Document\PrintLetter::class);

        $this->mockedSmServices[ApiSrv\Document\PrintLetter::class] = $this->mockPrintSrv;

        $this->sut = new PrintLetter();

        $this->refData = [Entity\Letter\LetterInstance::STATUS_SENT];

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

        $this->assertEquals($expect['result'], $actual->getMessages());
    }

    public static function dpTestHandleCommand(): \Iterator
    {
        yield 'method:Email;canEmail&Print;' => [
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
        ];
        yield 'method:Print;canEmail&Print;' => [
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
        ];
        yield 'method:Email;canNotEmail&Print;' => [
            'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
            'canDo' => [
                'email' => false,
                'print' => true,
            ],
            'expect' => [
                'result' => [],
                'assert' => [],
            ],
        ];
        yield 'method:Print;canEmail&NotPrint;addTranslation' => [
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
        ];
        yield 'method:Email;canEmail&NotPrint;addTranslation' => [
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

    /**
     * A letter from the letter builder is sent once its document is printed or emailed, which locks it
     * against further changes (VOL-7735 PR review: nothing ever marked a letter as sent)
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpDispatched')]
    public function testDispatchingMarksTheLetterAsSent(string $method): void
    {
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_READY);
        $letter->setDocument($this->mockDocE);
        $this->dispatch($method, ['email' => true, 'print' => true], $letter);

        $this->assertTrue($letter->isSent());
        $this->assertInstanceOf(\DateTime::class, $letter->getSentOn());
    }

    public static function dpDispatched(): array
    {
        return [
            'email' => [TransferCmd\Document\PrintLetter::METHOD_EMAIL],
            'print and post' => [TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST],
        ];
    }

    public function testALetterThatIsNotDispatchedIsNotMarkedAsSent(): void
    {
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_READY);
        $letter->setDocument($this->mockDocE);
        // can't print (Welsh translation needed), so it goes to a translation task instead
        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, ['email' => true, 'print' => false], $letter, false);

        $this->assertTrue($letter->isReady());
        $this->assertNull($letter->getSentOn());
    }

    public function testResendingAnAlreadySentLetterKeepsWhenItWasFirstSent(): void
    {
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_SENT);
        $letter->setDocument($this->mockDocE);
        $firstSent = new \DateTime('2026-01-01');
        $letter->setSentOn($firstSent);
        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, ['email' => true, 'print' => true], $letter, false);

        $this->assertSame($firstSent, $letter->getSentOn());
    }

    private function letterInstance(string $status): Entity\Letter\LetterInstance
    {
        $letter = new Entity\Letter\LetterInstance();
        $letter->setStatus(new Entity\System\RefData($status));

        return $letter;
    }

    private function dispatch(string $method, array $canDo, Entity\Letter\LetterInstance $letter, bool $expectSave = true): void
    {
        $command = TransferCmd\Document\PrintLetter::create(['id' => self::DOC_ID, 'method' => $method]);
        $this->mockDocRepo->shouldReceive('fetchUsingId')->andReturn($this->mockDocE);
        $this->mockPrintSrv
            ->shouldReceive('canPrint')->andReturn($canDo['print'])
            ->shouldReceive('canEmail')->andReturn($canDo['email']);

        $this->commandHandler->shouldReceive('handleCommand')->andReturn(new Result());
        $this->mockLetterInstanceRepo->shouldReceive('fetchByDocument')->with(self::DOC_ID)->andReturn($letter);
        $this->mockLetterInstanceRepo->shouldReceive('save')->with($letter)->times($expectSave ? 1 : 0);

        $this->sut->handleCommand($command);
    }

    /**
     * PR review: preparing a letter again makes a new PDF; the older one is out of date and can't be
     * sent, or it would go out without locking the letter
     */
    public function testAnOlderPreparedPdfOfALetterIsRefused(): void
    {
        $this->mockDocE->setMetadata(json_encode(['details' => ['documentTemplate' => 3, 'letterInstance' => 5]]));
        $newer = m::mock(Entity\Doc\Document::class)->makePartial();
        $newer->setId(self::DOC_ID + 1);
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_READY);
        $letter->setDocument($newer);

        $this->expectRefused($letter, 'A newer version of this letter has been prepared');
    }

    /**
     * PR review: a letter changed after it was prepared goes back to draft; its PDF no longer
     * matches, so it can't be sent until it's prepared again
     */
    public function testAPdfOfALetterChangedSinceItWasPreparedIsRefused(): void
    {
        $this->mockDocE->setMetadata(json_encode(['details' => ['documentTemplate' => 3, 'letterInstance' => 5]]));
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_DRAFT);
        $letter->setDocument($this->mockDocE);

        $this->expectRefused($letter, 'This letter has changed since it was prepared');
    }

    public function testTheLetterIsFoundFromThePdfsOwnRecordOfIt(): void
    {
        $this->mockDocE->setMetadata(json_encode(['details' => ['documentTemplate' => 3, 'letterInstance' => 5]]));
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_READY);
        $letter->setDocument($this->mockDocE);
        // only the PDF's own record can find it: the by-document lookup finds nothing
        $this->mockLetterInstanceRepo->shouldReceive('fetchById')->with(5)->andReturn($letter);
        $this->mockDocRepo->shouldReceive('fetchUsingId')->andReturn($this->mockDocE);
        $this->mockPrintSrv->shouldReceive('canPrint')->andReturn(true)->shouldReceive('canEmail')->andReturn(true);
        $this->commandHandler->shouldReceive('handleCommand')->andReturn(new Result());
        $this->mockLetterInstanceRepo->shouldReceive('save')->with($letter)->once();

        $this->sut->handleCommand(TransferCmd\Document\PrintLetter::create([
            'id' => self::DOC_ID,
            'method' => TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST,
        ]));

        $this->assertTrue($letter->isSent());
    }

    private function expectRefused(Entity\Letter\LetterInstance $letter, string $message): void
    {
        $this->mockDocRepo->shouldReceive('fetchUsingId')->andReturn($this->mockDocE);
        $this->mockPrintSrv->shouldReceive('canPrint')->andReturn(true)->shouldReceive('canEmail')->andReturn(true);
        $this->mockLetterInstanceRepo->shouldReceive('fetchById')->with(5)->andReturn($letter);
        $this->mockLetterInstanceRepo->shouldReceive('save')->never();
        $this->commandHandler->shouldReceive('handleCommand')->never();

        try {
            $this->sut->handleCommand(TransferCmd\Document\PrintLetter::create([
                'id' => self::DOC_ID,
                'method' => TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST,
            ]));
            $this->fail('Expected the send to be refused');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertStringContainsString($message, implode(' ', $e->getMessages()));
        }
    }
}
