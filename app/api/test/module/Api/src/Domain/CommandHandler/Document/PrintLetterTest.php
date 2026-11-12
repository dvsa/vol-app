<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\PrintLetter;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
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
    /** @var list<class-string> the commands a send set off, other than those a test expects by name */
    private array $setOff = [];

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

    public function testQueuesSmsAfterEmail(): void
    {
        $this->assertSendEmail();
        $this->runHandler(TransferCmd\Document\PrintLetter::METHOD_EMAIL, true, true);
    }

    public function testQueuesSmsAfterPrint(): void
    {
        $this->assertAttemptPrint();
        $this->runHandler(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, true, true);
    }

    public function testNoSmsWhenOnlyTranslationTask(): void
    {
        $this->assertCreateTranslationTask();
        $this->runHandler(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, false, true);
    }

    private function runHandler(string $method, bool $canPrint, bool $canEmail): void
    {
        $command = TransferCmd\Document\PrintLetter::create(['id' => self::DOC_ID, 'method' => $method]);
        $this->mockDocRepo->shouldReceive('fetchUsingId')->with($command)->andReturn($this->mockDocE);
        $this->mockPrintSrv
            ->shouldReceive('canPrint')->andReturn($canPrint)
            ->shouldReceive('canEmail')->andReturn($canEmail);

        $this->sut->handleCommand($command);
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
        $this->assertQueueSms();
    }

    private function assertQueueSms(): void
    {
        $this->expectedSideEffect(DomainCmd\Sms\QueueLetterSms::class, ['document' => self::DOC_ID], new Result());
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
        $this->assertQueueSms();
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

    /**
     * A first or final request letter gets chased: when it's sent, the caseworker who wrote it gets
     * a task to check for the response in 14 days (VOL-7714), as the older template letters do
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpRequestLetters')]
    public function testSendingAFirstOrFinalRequestLetterCreatesATaskToCheckForTheResponse(
        string $choiceKey,
        string $method,
        int $subCategory,
        string $description
    ): void {
        $letter = $this->requestLetter($choiceKey);

        $this->expectedSideEffect(
            DomainCmd\Task\CreateTask::class,
            [
                'category' => 9,
                'subCategory' => $subCategory,
                'description' => $description,
                'actionDate' => (new DateTime())->add(new \DateInterval('P14D'))->format('Y-m-d'),
                'assignedToUser' => 291,
                'assignedByUser' => 291,
                'urgent' => 'N',
                'application' => 55,
                'licence' => 66,
            ],
            new Result()
        );

        $this->dispatch($method, ['email' => true, 'print' => true], $letter);

        // one task and no more: a second would not match the expectation above and land here
        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    public static function dpRequestLetters(): array
    {
        $email = TransferCmd\Document\PrintLetter::METHOD_EMAIL;
        $post = TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST;

        return [
            'first request, emailed' => ['first', $email, 22, 'Check response to first letters'],
            'first request, posted' => ['first', $post, 22, 'Check response to first letters'],
            'final request, emailed' => ['final', $email, 23, 'Check response to final letters'],
            'final request, posted' => ['final', $post, 23, 'Check response to final letters'],
        ];
    }

    public function testALetterThatIsNotAFirstOrFinalRequestCreatesNoTask(): void
    {
        $letter = $this->requestLetter('time-limited-interims');

        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_EMAIL, ['email' => true, 'print' => true], $letter);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    /**
     * The task is for chasing an application. A letter written from a licence, with no application,
     * has nothing to put it on (the older template letters refuse to be made without one)
     */
    public function testARequestLetterThatIsNotOnAnApplicationCreatesNoTask(): void
    {
        $letter = $this->requestLetter('first');
        $letter->setApplication(null);

        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_EMAIL, ['email' => true, 'print' => true], $letter);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    /**
     * Sending must never fail for want of a writer: the task is still made, and task allocation
     * decides who gets it
     */
    public function testARequestLetterWithNoRecordedWriterStillGetsItsTask(): void
    {
        $letter = $this->requestLetter('final');
        $letter->setCreatedBy(null);

        $this->expectedSideEffect(
            DomainCmd\Task\CreateTask::class,
            ['subCategory' => 23, 'application' => 55, 'assignedToUser' => null, 'assignedByUser' => null],
            new Result()
        );

        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_EMAIL, ['email' => true, 'print' => true], $letter);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    /**
     * The writer's account can be deleted after they write the letter and before someone else
     * sends it. Only the id of a deleted user can be read, and giving them the task made the send
     * fail every time. Their letter is treated like one with no writer on record.
     *
     * The stand-in fails on the team, which is the read that broke the send: creating a task
     * looks up the team of the user it's for.
     */
    public function testARequestLetterWhoseWriterHasBeenDeletedStillGetsItsTask(): void
    {
        $writer = m::mock(Entity\User\User::class);
        $writer->shouldReceive('getId')->andReturn(291);
        $writer->shouldReceive('getTeam')->andThrow(
            \Doctrine\ORM\EntityNotFoundException::fromClassNameAndIdentifier(Entity\User\User::class, ['id' => '291'])
        );
        $letter = $this->requestLetter('first');
        $letter->setCreatedBy($writer);

        $this->expectedSideEffect(
            DomainCmd\Task\CreateTask::class,
            ['subCategory' => 22, 'application' => 55, 'assignedToUser' => null, 'assignedByUser' => null],
            new Result()
        );

        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_EMAIL, ['email' => true, 'print' => true], $letter);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    public function testResendingARequestLetterDoesNotCreateAnotherTask(): void
    {
        $letter = $this->requestLetter('first', Entity\Letter\LetterInstance::STATUS_SENT);
        $letter->setSentOn(new \DateTime('2026-01-01'));

        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, ['email' => true, 'print' => true], $letter, false);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    public function testARequestLetterThatDoesNotGoOutCreatesNoTask(): void
    {
        $letter = $this->requestLetter('first');

        // can't print (Welsh translation needed), so it goes to a translation task instead
        $this->dispatch(TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST, ['email' => true, 'print' => false], $letter, false);

        $this->assertNotContains(DomainCmd\Task\CreateTask::class, $this->setOff);
    }

    /**
     * A prepared letter on application 55 (licence 66), written by user 291, with one choice made
     */
    private function requestLetter(
        string $choiceKey,
        string $status = Entity\Letter\LetterInstance::STATUS_READY
    ): Entity\Letter\LetterInstance {
        $licence = m::mock(Entity\Licence\Licence::class);
        $licence->shouldReceive('getId')->andReturn(66);
        $application = m::mock(Entity\Application\Application::class);
        $application->shouldReceive('getId')->andReturn(55);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $writer = new Entity\User\User('pid', Entity\User\User::USER_TYPE_INTERNAL);
        $writer->setId(291);

        $choice = new Entity\Letter\LetterChoice();
        $choice->setChoiceKey($choiceKey);
        $instanceChoice = new Entity\Letter\LetterInstanceChoice();
        $instanceChoice->setLetterChoice($choice);

        $letter = $this->letterInstance($status);
        $letter->setDocument($this->mockDocE);
        $letter->setApplication($application);
        $letter->setCreatedBy($writer);
        $letter->addLetterInstanceChoice($instanceChoice);

        return $letter;
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

        $this->commandHandler->shouldReceive('handleCommand')->andReturnUsing(
            function ($sideEffect): Result {
                $this->setOff[] = $sideEffect::class;

                return new Result();
            }
        );
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

    /**
     * PR review: the letter is marked sent before anything goes out, so an edit that lands in
     * between fails the send (the letter's version has moved on) before an email or print is
     * queued or a task is made
     */
    public function testTheLetterIsLockedBeforeAnythingGoesOut(): void
    {
        // a first request letter, so its task counts as something going out too
        $letter = $this->requestLetter('first');
        $this->mockDocRepo->shouldReceive('fetchUsingId')->andReturn($this->mockDocE);
        $this->mockPrintSrv->shouldReceive('canPrint')->andReturn(true)->shouldReceive('canEmail')->andReturn(true);
        $this->mockLetterInstanceRepo->shouldReceive('fetchByDocument')->with(self::DOC_ID)->andReturn($letter);
        $this->mockLetterInstanceRepo->shouldReceive('save')->with($letter)->once()
            ->andThrow(new \Doctrine\ORM\OptimisticLockException('edited meanwhile', $letter));
        $this->commandHandler->shouldReceive('handleCommand')->never();

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('This letter was changed somewhere else at the same time');
        $this->sut->handleCommand(TransferCmd\Document\PrintLetter::create([
            'id' => self::DOC_ID,
            'method' => TransferCmd\Document\PrintLetter::METHOD_EMAIL,
        ]));
    }

    /**
     * Review: only a prepared (or already sent) letter goes out; a cancelled one isn't marked sent
     */
    public function testACancelledLetterIsNotSent(): void
    {
        $this->mockDocE->setMetadata(json_encode(['details' => ['documentTemplate' => 3, 'letterInstance' => 5]]));
        $letter = $this->letterInstance(Entity\Letter\LetterInstance::STATUS_CANCELLED);
        $letter->setDocument($this->mockDocE);

        $this->expectRefused($letter, 'This letter has been cancelled');
    }
}
