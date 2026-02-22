<?php

declare(strict_types=1);

/**
 * Confirm printing
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GoodsDisc;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\GoodsDisc\ConfirmPrinting;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\GoodsDisc\ConfirmPrinting as Cmd;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * Confirm printing
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ConfirmPrintingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ConfirmPrinting();
        $this->mockRepo('DiscSequence', DiscSequenceRepo::class);
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);
        $this->mockRepo('Queue', QueueRepo::class);

        parent::setUp();
    }

    public function testHandleCommandWithAssigningNumber(): void
    {
        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $startNumber = 1;
        $endNumber = 5;
        $discSequence = 2;
        $queueId = 1;
        $options = '{"discs":[1,2,3],"type":"Goods","startNumber":"455705","user":273}';

        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'endNumber' => $endNumber,
            'isSuccessfull' => true,
            'queueId' => $queueId
        ];
        $command = Cmd::create($data);

        $mockQueue = m::mock()
            ->shouldReceive('getOptions')
            ->andReturn($options)
            ->once()
            ->getMock();

        $this->repoMap['Queue']
            ->shouldReceive('fetchById')
            ->with($queueId)
            ->once()
            ->andReturn($mockQueue)
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('enableSoftDeleteable')
            ->once()
            ->getMock();

        $this->repoMap['GoodsDisc']
            ->shouldReceive('setIsPrintingOffAndAssignNumbers')
            ->with([1, 2, 3], $startNumber)
            ->once()
            ->getMock();

        $mockDiscSequence = m::mock()
            ->shouldReceive('setDiscStartNumber')
            ->with($licenceType, $endNumber + 1)
            ->once()
            ->getMock();

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchById')
            ->with($discSequence)
            ->andReturn($mockDiscSequence)
            ->shouldReceive('save')
            ->with($mockDiscSequence)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => [
                'Printing flag is now off and numbers assigned to discs'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithSetPrintingOff(): void
    {
        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $startNumber = 1;
        $endNumber = 5;
        $discSequence = 2;
        $queueId = 1;
        $options = '{"discs":[1,2,3],"type":"Goods","startNumber":"455705","user":273}';
        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'endNumber' => $endNumber,
            'isSuccessfull' => false,
            'queueId' => $queueId
        ];
        $command = Cmd::create($data);

        $mockQueue = m::mock()
            ->shouldReceive('getOptions')
            ->andReturn($options)
            ->once()
            ->getMock();

        $this->repoMap['Queue']
            ->shouldReceive('fetchById')
            ->with($queueId)
            ->once()
            ->andReturn($mockQueue)
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('enableSoftDeleteable')
            ->once()
            ->getMock();

        $this->repoMap['GoodsDisc']
            ->shouldReceive('setIsPrintingOff')
            ->with([1, 2, 3])
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => [
                'Printing flag is now off'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(RuntimeException::class);

        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $startNumber = 1;
        $endNumber = 5;
        $discSequence = 2;
        $queueId = 1;
        $options = '{"type":"Goods","startNumber":"455705","user":273}';

        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'endNumber' => $endNumber,
            'isSuccessfull' => true,
            'queueId' => $queueId
        ];
        $command = Cmd::create($data);

        $mockQueue = m::mock()
            ->shouldReceive('getOptions')
            ->andReturn($options)
            ->once()
            ->getMock();

        $this->repoMap['Queue']
            ->shouldReceive('fetchById')
            ->with($queueId)
            ->once()
            ->andReturn($mockQueue)
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
