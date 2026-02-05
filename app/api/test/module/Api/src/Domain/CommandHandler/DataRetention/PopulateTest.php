<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\Populate;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Populate as Cmd;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class PopulateTest
 */
class PopulateTest extends AbstractCommandHandlerTestCase
{
    private $mockedConnection;

    public function setUp(): void
    {
        $this->sut = new Populate();
        $this->mockRepo('DataRetentionRule', DataRetentionRule::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedConnection = m::mock(Connection::class);
        $this->mockedConnection->shouldReceive('beginTransaction');
        $this->mockedSmServices['doctrine.entitymanager.orm_default'] = m::mock(EntityManager::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default']
            ->shouldReceive('getConnection')
            ->andReturn($this->mockedConnection);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $dataRetentionRules = [
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(2)->setPopulateProcedure('proc2'),
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(9)->setPopulateProcedure('proc9'),
        ];

        $command = Cmd::create([]);
        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchEnabledRules')
            ->with()
            ->once()
            ->andReturn(
                ['results' => $dataRetentionRules]
            )
            ->shouldReceive('runProc')->with('proc2', 222)->once()->andReturn(true)
            ->shouldReceive('runProc')->with('proc9', 222)->once()->andReturn(true);

        /** @var User $currentUser */
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->setId(222);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);
        $this->mockedConnection->shouldReceive('commit')->twice();
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Running rule id 2, proc2',
                'Running rule id 9, proc9',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenProcedureReturnsFalse(): void
    {
        $dataRetentionRules = [
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(2)->setPopulateProcedure('proc2'),
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(9)->setPopulateProcedure('proc9'),
        ];

        $command = Cmd::create([]);
        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchEnabledRules')
            ->with()
            ->once()
            ->andReturn(
                ['results' => $dataRetentionRules]
            )
            ->shouldReceive('runProc')->with('proc2', 222)->once()->andReturn(true)
            ->shouldReceive('runProc')->with('proc9', 222)->once()->andReturn(false);

        /** @var User $currentUser */
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->setId(222);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);
        $this->mockedConnection->shouldReceive('commit')->twice();
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Running rule id 2, proc2',
                'Running rule id 9, proc9',
                'Rule id 9, proc9 returned FALSE'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenProcedureThrowsException(): void
    {
        $dataRetentionRules = [
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(2)->setPopulateProcedure('proc2'),
            (new \Dvsa\Olcs\Api\Entity\DataRetentionRule())->setId(9)->setPopulateProcedure('proc9'),
        ];

        $command = Cmd::create([]);
        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchEnabledRules')
            ->with()
            ->once()
            ->andReturn(
                ['results' => $dataRetentionRules]
            )
            ->shouldReceive('runProc')->with('proc2', 222)->once()->andReturn(true)
            ->shouldReceive('runProc')->with('proc9', 222)->once()->andThrow(new \Exception('proc9 Error'));

        /** @var User $currentUser */
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->setId(222);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);
        $this->mockedConnection->shouldReceive('commit')->once();
        $this->mockedConnection->shouldReceive('rollBack')->once();
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Running rule id 2, proc2',
                'Running rule id 9, proc9',
                'proc9 Error'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
