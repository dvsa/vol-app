<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\EditSuspension;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\EditSuspention;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspension as CommunityLicSuspensionRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspensionReason as CommunityLicSuspensionReasonRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommunityLic\EditSuspension as Cmd;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension as CommunityLicSuspensionEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as CommunityLicSuspensionReasonEntity;
use Doctrine\ORM\Query;

/**
 * Edit Suspension Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EditSuspensionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EditSuspension();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('CommunityLicSuspension', CommunityLicSuspensionRepo::class);
        $this->mockRepo('CommunityLicSuspensionReason', CommunityLicSuspensionReasonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_ACTIVE,
            CommunityLicEntity::STATUS_PENDING,
            CommunityLicEntity::STATUS_WITHDRAWN,
            CommunityLicEntity::STATUS_SUSPENDED,
            'reason'
        ];

        $this->references = [
            CommunityLicSuspensionEntity::class => [
                111 => m::mock(CommunityLicSuspensionEntity::class)
            ],
            CommunityLicEntity::class => [
                10 => m::mock(CommunityLicEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandActiveToday(): void
    {
        $startDate = (new \DateTime())->format('Y-m-d');
        $data = [
            'startDate' => $startDate,
            'endDate' => '3016-01-01',
            'status' => CommunityLicEntity::STATUS_ACTIVE,
            'communityLicenceId' => 1,
            'id' => 2,
            'version' => 3,
            'reasons' => ['bar']
        ];
        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('setStatus')
            ->with($this->refData[CommunityLicEntity::STATUS_SUSPENDED])
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockCommunityLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once();

        $this->mockUpdateSuspensionAndReasons($command, $mockCommunityLicence, $startDate);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($result->getMessages(), ['The community licence has been suspended']);
    }

    protected function mockUpdateSuspensionAndReasons(mixed $command, mixed $mockCommunityLicence, mixed $startDate): void
    {
        $mockCommunityLicSuspension = m::mock(CommunityLicSuspensionEntity::class)
            ->shouldReceive('updateCommunityLicSuspension')
            ->with($mockCommunityLicence, $startDate, '3016-01-01')
            ->once()
            ->shouldReceive('getCommunityLicSuspensionReasons')
            ->andReturn(['foo'])
            ->once()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspensionReason']
            ->shouldReceive('delete')
            ->with('foo')
            ->once()
            ->shouldReceive('save')
            ->with(m::type(CommunityLicSuspensionReasonEntity::class))
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspension']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 3)
            ->once()
            ->andReturn($mockCommunityLicSuspension)
            ->shouldReceive('save')
            ->with($mockCommunityLicSuspension)
            ->once()
            ->getMock();
    }

    public function testHandleCommandActiveInFuture(): void
    {
        $startDate = '3015-01-01';
        $data = [
            'startDate' => $startDate,
            'endDate' => '3016-01-01',
            'status' => CommunityLicEntity::STATUS_ACTIVE,
            'communityLicenceId' => 1,
            'id' => 2,
            'version' => 3,
            'reasons' => ['bar']
        ];
        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class);

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockCommunityLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once();

        $this->mockUpdateSuspensionAndReasons($command, $mockCommunityLicence, $startDate);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($result->getMessages(), ['The community licence suspension details have been updated']);
    }

    public function testHandleCommandSuspendedNotToday(): void
    {
        $startDate = '3015-01-01';
        $data = [
            'startDate' => $startDate,
            'endDate' => '3016-01-01',
            'status' => CommunityLicEntity::STATUS_SUSPENDED,
            'communityLicenceId' => 1,
            'id' => 2,
            'version' => 3,
            'reasons' => ['bar']
        ];
        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class);

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockCommunityLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once();

        $this->mockUpdateSuspensionAndReasons($command, $mockCommunityLicence, $startDate);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($result->getMessages(), ['The community licence suspension details have been updated']);
    }

    public function testHandleCommandSuspendedToday(): void
    {
        $endDate = (new \DateTime())->format('Y-m-d');
        $data = [
            'startDate' => '2000-01-01',
            'endDate' => $endDate,
            'status' => CommunityLicEntity::STATUS_SUSPENDED,
            'communityLicenceId' => 1,
            'id' => 2,
            'version' => 3,
            'reasons' => ['bar']
        ];
        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('setStatus')
            ->with($this->refData[CommunityLicEntity::STATUS_ACTIVE])
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($mockCommunityLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once();

        $mockCommunityLicSuspension = m::mock(CommunityLicSuspensionEntity::class)
            ->shouldReceive('getCommunityLicSuspensionReasons')
            ->andReturn(['foo'])
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspensionReason']
            ->shouldReceive('delete')
            ->with('foo')
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspension']
            ->shouldReceive('fetchById')
            ->with(2)
            ->once()
            ->andReturn($mockCommunityLicSuspension)
            ->shouldReceive('delete')
            ->with($mockCommunityLicSuspension)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($result->getMessages(), ['The community licence has been restored to active']);
    }
}
