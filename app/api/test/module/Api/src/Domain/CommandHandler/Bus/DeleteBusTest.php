<?php

declare(strict_types=1);

/**
 * Delete Bus Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\DeleteBus;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\DeleteBus as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Delete Bus Test
 */
class DeleteBusTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteBus();
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('TxcInbox', TxcInboxRepo::class);
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;
        $previousId = 98;

        $routeNo = 11;
        $variationNo = 22;
        $licenceId = 33;

        $command = Cmd::Create(['id' => $id,]);

        $mockEbsrSubmissionList = new ArrayCollection(['ebsrSubmission1']);
        $mockTxcInboxList = new ArrayCollection(['txcInbox1']);

        /** @var BusEntity $busReg */
        $busReg = m::mock(BusEntity::class);
        $busReg->shouldReceive('getRouteNo')
            ->once()
            ->andReturn($routeNo)
            ->shouldReceive('getVariationNo')
            ->once()
            ->andReturn($variationNo)
            ->shouldReceive('canDelete')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getEbsrSubmissions')
            ->once()
            ->andReturn($mockEbsrSubmissionList)
            ->shouldReceive('getTxcInboxs')
            ->once()
            ->andReturn($mockTxcInboxList)
            ->shouldReceive('getLicence->getId')
            ->once()
            ->andReturn($licenceId);

        $mockPreviousBusReg = m::mock(BusEntity::class);
        $mockPreviousBusReg->shouldReceive('getId')-> andReturn($previousId);

        $mockFetchList = new \ArrayIterator([0 => $mockPreviousBusReg]);

        $this->repoMap['TxcInbox']->shouldReceive('delete')->with('txcInbox1');
        $this->repoMap['EbsrSubmission']->shouldReceive('delete')->with('ebsrSubmission1');
        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($busReg)
            ->shouldReceive('fetchList')
            ->once()
            ->andReturn($mockFetchList)
            ->shouldReceive('delete')
            ->with(m::type(BusEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($previousId, $result->getId('previousBusRegId'));
    }
}
