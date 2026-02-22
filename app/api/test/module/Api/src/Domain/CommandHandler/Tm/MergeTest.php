<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\Merge as CommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\Tm\Merge as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class MergeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MergeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManager', \Dvsa\Olcs\Api\Domain\Repository\TransportManager::class);
        $this->mockRepo('Task', \Dvsa\Olcs\Api\Domain\Repository\Task::class);
        $this->mockRepo('Note', \Dvsa\Olcs\Api\Domain\Repository\Note::class);
        $this->mockRepo('EventHistory', \Dvsa\Olcs\Api\Domain\Repository\EventHistory::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransportManagerEntity::TRANSPORT_MANAGER_STATUS_REMOVED,
        ];
        parent::initReferences();
    }

    public function testHandleCommandDonorSameAsRecipient(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 3,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->twice()->andReturn($mockDonorTm);

        try {
            $this->sut->handleCommand($command);
            $this->fail('ValidationException should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('TM_MERGE_DONAR_RECIPIENT_SAME', $e->getMessages());
        }
    }

    public function testHandleCommandDonorAndRecipientHaveUsers(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();

        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $mockDonorTm->addUsers($user);
        $mockRecipientTm->addUsers($user);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        try {
            $this->sut->handleCommand($command);
            $this->fail('ValidationException should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('TM_MERGE_BOTH_HAVE_USER_ACCOUNTS', $e->getMessages());
        }
    }

    public function testHandleCommandDonorAlreadyMerged(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => true
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();

        $mockDonorTm->setMergeToTransportManager(new \Dvsa\Olcs\Api\Entity\Tm\TransportManager());

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        try {
            $this->sut->handleCommand($command);
            $this->fail('ValidationException should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('TM_MERGE_ALREADY_MERGED', $e->getMessages());
        }
    }

    public function testHandleCommandLva(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $stubLicence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class);
        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $tml1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence($stubLicence, $mockDonorTm);
        $tml1->setId(173);
        $mockDonorTm->addTmLicences($tml1);
        $tml2 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence($stubLicence, $mockDonorTm);
        $tml2->setId(273);
        $mockDonorTm->addTmLicences($tml2);

        $tma1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma1->setTransportManager($mockDonorTm)
            ->setId(124);
        $mockDonorTm->addTmApplications($tma1);
        $tma2 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma2->setTransportManager($mockDonorTm)
            ->setId(224);
        $mockDonorTm->addTmApplications($tma2);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence::class => [173,273],
                \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication::class => [124,224]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());
        $this->assertEquals(
            $this->refData[TransportManagerEntity::TRANSPORT_MANAGER_STATUS_REMOVED],
            $mockDonorTm->getTmStatus()
        );

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCases(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $case1 = new \Dvsa\Olcs\Api\Entity\Cases\Cases(
            new DateTime(),
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            new \Doctrine\Common\Collections\ArrayCollection(),
            new \Doctrine\Common\Collections\ArrayCollection(),
            null,
            null,
            null,
            null,
            null
        );
        $case1->setId(148);
        $mockDonorTm->addCases($case1);
        $case2 = new \Dvsa\Olcs\Api\Entity\Cases\Cases(
            new DateTime(),
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            new \Doctrine\Common\Collections\ArrayCollection(),
            new \Doctrine\Common\Collections\ArrayCollection(),
            null,
            null,
            null,
            null,
            null
        );
        $case2->setId(248);
        $mockDonorTm->addCases($case2);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [\Dvsa\Olcs\Api\Entity\Cases\Cases::class => [148, 248]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDocuments(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $document1 = new \Dvsa\Olcs\Api\Entity\Doc\Document(1);
        $document1->setId(176);
        $mockDonorTm->addDocuments($document1);
        $document2 = new \Dvsa\Olcs\Api\Entity\Doc\Document(2);
        $document2->setId(276);
        $mockDonorTm->addDocuments($document2);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [\Dvsa\Olcs\Api\Entity\Doc\Document::class => [176, 276]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandTasks(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $task1 = new \Dvsa\Olcs\Api\Entity\Task\Task(
            new \Dvsa\Olcs\Api\Entity\System\Category(),
            new \Dvsa\Olcs\Api\Entity\System\SubCategory()
        );
        $task1->setId(118);
        $task2 = new \Dvsa\Olcs\Api\Entity\Task\Task(
            new \Dvsa\Olcs\Api\Entity\System\Category(),
            new \Dvsa\Olcs\Api\Entity\System\SubCategory()
        );
        $task2->setId(218);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([$task1, $task2]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [\Dvsa\Olcs\Api\Entity\Task\Task::class => [118, 218]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNotes(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $note1 = new \Dvsa\Olcs\Api\Entity\Note\Note();
        $note1->setId(184);
        $note2 = new \Dvsa\Olcs\Api\Entity\Note\Note();
        $note2->setId(284);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([$note1, $note2]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [\Dvsa\Olcs\Api\Entity\Note\Note::class => [184, 284]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandEventHistory(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $ev1 = m::mock(\Dvsa\Olcs\Api\Entity\EventHistory\EventHistory::class)->makePartial();
        $ev1->setId(172);
        $ev2 = m::mock(\Dvsa\Olcs\Api\Entity\EventHistory\EventHistory::class)->makePartial();
        $ev2->setId(272);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([$ev1, $ev2]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [[172, 272]],
            array_values($mockDonorTm->getMergeDetails())
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandUserAccount(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => false
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockDonorTm->setId(3);
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setId(9);

        $user = new UserEntity('', UserEntity::USER_TYPE_INTERNAL);
        $user->setId(115);
        $mockDonorTm->addUsers($user);
        $this->expectedUserCacheClear([115]);

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        $this->repoMap['Task']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);
        $this->repoMap['EventHistory']->shouldReceive('fetchByTransportManager')->with($mockDonorTm)->once()
            ->andReturn([]);

        $this->repoMap['TransportManager']->shouldReceive('save')->once()->with($mockDonorTm);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [UserEntity::class => [115]],
            $mockDonorTm->getMergeDetails()
        );
        $this->assertSame($mockRecipientTm, $mockDonorTm->getMergeToTransportManager());
        $this->assertEquals(new DateTime(), $mockDonorTm->getRemovedDate());

        $expected = [
            'id' => [],
            'messages' => ['Merged Transport Manager id 3 into TransportManager id 9.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandRecipientAlreadyRemoved(): void
    {
        $data = [
            'id' => 3,
            'recipientTransportManager' => 9,
            'confirm' => true
        ];

        $mockDonorTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $mockRecipientTm->setRemovedDate('2015-01-01');

        $mockDonorTm->setMergeToTransportManager(new \Dvsa\Olcs\Api\Entity\Tm\TransportManager());

        $command = Cmd::create($data);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(3)->once()->andReturn($mockDonorTm);
        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(9)->once()->andReturn($mockRecipientTm);

        try {
            $this->sut->handleCommand($command);
            $this->fail('ValidationException should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('TM_MERGE_RECIPIENT_REMOVED', $e->getMessages());
        }
    }
}
