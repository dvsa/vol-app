<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\Unmerge as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Command\Tm\Unmerge as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class UnmergeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UnmergeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManager', \Dvsa\Olcs\Api\Domain\Repository\TransportManager::class);
        $this->mockRepo(
            'TransportManagerApplication',
            \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication::class
        );
        $this->mockRepo('TransportManagerLicence', \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence::class);
        $this->mockRepo('Cases', \Dvsa\Olcs\Api\Domain\Repository\Cases::class);
        $this->mockRepo('Document', \Dvsa\Olcs\Api\Domain\Repository\Document::class);
        $this->mockRepo('Task', \Dvsa\Olcs\Api\Domain\Repository\Task::class);
        $this->mockRepo('Note', \Dvsa\Olcs\Api\Domain\Repository\Note::class);
        $this->mockRepo('EventHistory', \Dvsa\Olcs\Api\Domain\Repository\EventHistory::class);
        $this->mockRepo('User', \Dvsa\Olcs\Api\Domain\Repository\User::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransportManagerEntity::TRANSPORT_MANAGER_STATUS_CURRENT,
        ];
        parent::initReferences();
    }

    public function testHandleCommandNotMerged(): void
    {
        $data = [
            'id' => 3,
        ];

        $mockTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockTm);

        $command = Cmd::create($data);
        try {
            $this->sut->handleCommand($command);
            $this->fail('ValidationException should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('TM_UNMERGE_NOT_MERGED', $e->getMessages());
        }
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 3,
        ];

        $mockTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockTm->setId(3)
            ->setMergeToTransportManager(new \Dvsa\Olcs\Api\Entity\Tm\TransportManager())
            ->setRemovedDate('SOMETHING');
        $mergeDetails = [
            \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication::class => [12, 13],
            \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence::class => [14, 15],
            \Dvsa\Olcs\Api\Entity\Cases\Cases::class => [16, 17],
            \Dvsa\Olcs\Api\Entity\Doc\Document::class => [18, 19],
            \Dvsa\Olcs\Api\Entity\Task\Task::class => [20, 21, 22],
            \Dvsa\Olcs\Api\Entity\Note\Note::class => [22, 23],
            \Dvsa\Olcs\Api\Entity\EventHistory\EventHistory::class => [24, 25],
            \Dvsa\Olcs\Api\Entity\Proxy\__CG__\Dvsa\Olcs\Api\Entity\User\User::class => [26, 27],
        ];
        $mockTm->setMergeDetails($mergeDetails);

        $mockEntity = m::mock();
        $mockEntity->shouldReceive('setTransportManager')->with($mockTm)->times(16);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockTm);

        $this->repoMap['TransportManagerApplication']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(12)->once()
            ->andReturn($mockEntity);
        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(13)->once()
            ->andReturn($mockEntity);

        $this->repoMap['TransportManagerLicence']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchById')->with(14)->once()
            ->andReturn($mockEntity);
        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchById')->with(15)->once()
            ->andReturn($mockEntity);

        $this->repoMap['Cases']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['Cases']->shouldReceive('fetchById')->with(16)->once()->andReturn($mockEntity);
        $this->repoMap['Cases']->shouldReceive('fetchById')->with(17)->once()->andReturn($mockEntity);

        $this->repoMap['Document']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['Document']->shouldReceive('fetchById')->with(18)->once()->andReturn($mockEntity);
        $this->repoMap['Document']->shouldReceive('fetchById')->with(19)->once()->andReturn($mockEntity);

        $this->repoMap['Task']->shouldReceive('disableSoftDeleteable')->times(3);
        $this->repoMap['Task']->shouldReceive('fetchById')->with(20)->once()->andReturn($mockEntity);
        $this->repoMap['Task']->shouldReceive('fetchById')->with(21)->once()->andReturn($mockEntity);
        $this->repoMap['Task']->shouldReceive('fetchById')->with(22)->once()
            ->andReturnNull()->andThrow(Exception\NotFoundException::class)->andReturnNull();

        $this->repoMap['Note']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['Note']->shouldReceive('fetchById')->with(22)->once()->andReturn($mockEntity);
        $this->repoMap['Note']->shouldReceive('fetchById')->with(23)->once()->andReturn($mockEntity);

        $this->repoMap['EventHistory']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['EventHistory']->shouldReceive('fetchById')->with(24)->once()->andReturn($mockEntity);
        $this->repoMap['EventHistory']->shouldReceive('fetchById')->with(25)->once()->andReturn($mockEntity);

        $this->repoMap['User']->shouldReceive('disableSoftDeleteable')->twice();
        $this->repoMap['User']->shouldReceive('fetchById')->with(26)->once()->andReturn($mockEntity);
        $this->repoMap['User']->shouldReceive('fetchById')->with(27)->once()->andReturn($mockEntity);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockTm);

        $command = Cmd::create($data);
        $result = $this->sut->handleCommand($command);

        $this->assertNull($mockTm->getMergeDetails());
        $this->assertNull($mockTm->getMergeToTransportManager());
        $this->assertNull($mockTm->getRemovedDate());

        $this->assertEquals(
            $this->refData[TransportManagerEntity::TRANSPORT_MANAGER_STATUS_CURRENT],
            $mockTm->getTmStatus()
        );

        $expected = [
            'id' => [],
            'messages' => ['Unmerged Transport Manager id 3']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandUnknownEntity(): void
    {
        $data = [
            'id' => 3,
        ];

        $mockTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockTm->setId(3)
            ->setMergeToTransportManager(new \Dvsa\Olcs\Api\Entity\Tm\TransportManager())
            ->setRemovedDate('SOMETHING');
        $mergeDetails = [
            'FooBar' => [12, 13],
        ];
        $mockTm->setMergeDetails($mergeDetails);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockTm);

        $this->expectException(\RuntimeException::class);

        $command = Cmd::create($data);
        $this->sut->handleCommand($command);
    }
}
