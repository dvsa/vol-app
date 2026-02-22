<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackFailed as ProcessPackFailedCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackTransaction as ProcessPackTransactionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessPackFailed;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProcessPackFailedTest extends ProcessPackTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessPackFailed();
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'organisation' => 11,
            'id' => 12
        ];

        $command = ProcessPackFailedCmd::create($cmdData);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(ProcessPackTransactionCmd::class, false)
            ->andReturn(new Result());

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(CreateQueue::class, false)
            ->andReturn(new Result());

        $ebsrMock = m::mock(EbsrSubmission::class);
        $documentMock = m::mock(Document::class);
        $documentMock->shouldReceive('getDescription')->andReturn('description');
        $ebsrMock->shouldReceive('getDocument')->andReturn($documentMock);
        $ebsrMock->shouldReceive('finishValidating')
            ->with($this->refData[EbsrSubmission::FAILED_STATUS], ['an_array'])
            ->andReturnSelf();
        $ebsrMock->shouldReceive('getId')->andReturn(12);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($ebsrMock)
            ->shouldReceive('save')
            ->with($ebsrMock)
            ->andReturnSelf();

        $this->sut->handleCommand($command);
    }
}
