<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GeneratePermitDocuments;
use Dvsa\Olcs\Api\Domain\Command\Permits\ProceedToStatus;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Cli\Domain\Command\Permits\GeneratePermits;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\GeneratePermits as GeneratePermitsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Generate Permits Test
 */
class GeneratePermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GeneratePermitsHandler();

        parent::setUp();
    }

    public function testHandleWithoutIds(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('Empty list of permits provided.');

        $cmdData = [
            'ids' => [],
            'user' => 456,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->sut->handleCommand($command);
    }

    public function testHandleWithoutUser(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('No user provided.');

        $cmdData = [
            'ids' => [1, 2, 3],
            'user' => null,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleButIssueWithDocs')]
    public function testHandleButIssueWithDocs(mixed $permitDocs, mixed $letterDocs, mixed $expected): void
    {
        $ids = [1, 2, 3];
        $userId = 456;

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->times(2)
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->once();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $docsResult = new Result();
        if (isset($permitDocs)) {
            $docsResult->addId('permit', $permitDocs);
        }
        if (isset($letterDocs)) {
            $docsResult->addId('coveringLetter', $letterDocs);
        }
        $docsResult->addMessage('Docs generated');

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            $docsResult
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_ERROR,
            ],
            (new Result())->addMessage('Permits proceeded to Error')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expected);

        $this->sut->handleCommand($command);
    }

    public static function dpHandleButIssueWithDocs(): array
    {
        return [
            'no docs generated' => [
                null,
                null,
                'Permits generation failed with error: No documents generated.'
            ],
            'no permit docs generated' => [
                [],
                [201, 202, 203],
                'Permits generation failed with error: No permits generated.'
            ],
        ];
    }

    public function testHandleButIssueWithPermitsPrinting(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Permits generation failed with error: Permits printing failed.');

        $ids = [1, 2, 3];
        $userId = 456;
        $permitDocs = [101, 102, 103];
        $letterDocs = [201, 202, 203];

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->times(2)
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->once();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            (new Result())
                ->addId('permit', $permitDocs)
                ->addId('coveringLetter', $letterDocs)
                ->addMessage('Docs generated')
        );

        $this->expectedSideEffectThrowsException(
            Enqueue::class,
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => $permitDocs,
                'jobName' => 'Permits',
                'user' => $userId,
            ],
            new RuntimeException('Permits printing failed.')
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_ERROR,
            ],
            (new Result())->addMessage('Permits proceeded to Error')
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleButIssueWithLetterPrinting(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Permits generation failed with error: Letter printing failed.');

        $ids = [1, 2, 3];
        $userId = 456;
        $permitDocs = [101, 102, 103];
        $letterDocs = [201, 202, 203];

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->times(2)
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->once();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            (new Result())
                ->addId('permit', $permitDocs)
                ->addId('coveringLetter', $letterDocs)
                ->addMessage('Docs generated')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => $permitDocs,
                'jobName' => 'Permits',
                'user' => $userId,
            ],
            (new Result())->addMessage('Permits scheduled for printing')
        );

        $this->expectedSideEffectThrowsException(
            Enqueue::class,
            [
                'documentId' => $letterDocs[0],
                'jobName' => 'Permit covering letter',
                'user' => $userId,
            ],
            new RuntimeException('Letter printing failed.')
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_ERROR,
            ],
            (new Result())->addMessage('Permits proceeded to Error')
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $ids = [1, 2, 3];
        $userId = 456;
        $permitDocs = [101, 102, 103];
        $letterDocs = [201, 202, 203];

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->once()
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->never();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            (new Result())
                ->addId('permit', $permitDocs)
                ->addId('coveringLetter', $letterDocs)
                ->addMessage('Docs generated')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => $permitDocs,
                'jobName' => 'Permits',
                'user' => $userId,
            ],
            (new Result())->addMessage('Permits scheduled for printing')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'documentId' => $letterDocs[0],
                'jobName' => 'Permit covering letter',
                'user' => $userId,
            ],
            (new Result())->addMessage(sprintf('Letter %d scheduled for printing', $letterDocs[0]))
        );
        $this->expectedSideEffect(
            Enqueue::class,
            [
                'documentId' => $letterDocs[1],
                'jobName' => 'Permit covering letter',
                'user' => $userId,
            ],
            (new Result())->addMessage(sprintf('Letter %d scheduled for printing', $letterDocs[1]))
        );
        $this->expectedSideEffect(
            Enqueue::class,
            [
                'documentId' => $letterDocs[2],
                'jobName' => 'Permit covering letter',
                'user' => $userId,
            ],
            (new Result())->addMessage(sprintf('Letter %d scheduled for printing', $letterDocs[2]))
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTED,
            ],
            (new Result())->addMessage('Permits proceeded to Printed')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'permit' => $permitDocs,
                'coveringLetter' => $letterDocs,
            ],
            'messages' => [
                'Permits proceeded to Printing',
                'Docs generated',
                'Permits scheduled for printing',
                sprintf('Letter %d scheduled for printing', $letterDocs[0]),
                sprintf('Letter %d scheduled for printing', $letterDocs[1]),
                sprintf('Letter %d scheduled for printing', $letterDocs[2]),
                'Permits proceeded to Printed',
                'Permits generated',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutCoverLetter(): void
    {
        $ids = [1, 2, 3];
        $userId = 456;
        $permitDocs = [101, 102, 103];

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->once()
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->never();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            (new Result())
                ->addId('permit', $permitDocs)
                ->addMessage('Docs generated')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => $permitDocs,
                'jobName' => 'Permits',
                'user' => $userId,
            ],
            (new Result())->addMessage('Permits scheduled for printing')
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTED,
            ],
            (new Result())->addMessage('Permits proceeded to Printed')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'permit' => $permitDocs,
            ],
            'messages' => [
                'Permits proceeded to Printing',
                'Docs generated',
                'Permits scheduled for printing',
                'Permits proceeded to Printed',
                'Permits generated',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithOneItemList(): void
    {
        $ids = [1];
        $userId = 456;
        $permitDocs = 101;
        $letterDocs = 201000;

        $cmdData = [
            'ids' => $ids,
            'user' => $userId,
        ];

        $command = GeneratePermits::create($cmdData);

        $this->mockTransationMngr
            ->shouldReceive('beginTransaction')
            ->once()
            ->shouldReceive('commit')
            ->once()
            ->shouldReceive('rollback')
            ->never();

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTING,
            ],
            (new Result())->addMessage('Permits proceeded to Printing')
        );

        $this->expectedSideEffect(
            GeneratePermitDocuments::class,
            [
                'ids' => $ids,
            ],
            (new Result())
                ->addId('permit', $permitDocs)
                ->addId('coveringLetter', $letterDocs)
                ->addMessage('Docs generated')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'type' => Queue::TYPE_PERMIT_PRINT,
                'documents' => [$permitDocs],
                'jobName' => 'Permits',
                'user' => $userId,
            ],
            (new Result())->addMessage('Permits scheduled for printing')
        );

        $this->expectedSideEffect(
            Enqueue::class,
            [
                'documentId' => $letterDocs,
                'jobName' => 'Permit covering letter',
                'user' => $userId,
            ],
            (new Result())->addMessage(sprintf('Letter %d scheduled for printing', $letterDocs))
        );

        $this->expectedSideEffect(
            ProceedToStatus::class,
            [
                'ids' => $ids,
                'status' => IrhpPermit::STATUS_PRINTED,
            ],
            (new Result())->addMessage('Permits proceeded to Printed')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'permit' => $permitDocs,
                'coveringLetter' => $letterDocs,
            ],
            'messages' => [
                'Permits proceeded to Printing',
                'Docs generated',
                'Permits scheduled for printing',
                sprintf('Letter %d scheduled for printing', $letterDocs),
                'Permits proceeded to Printed',
                'Permits generated',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
