<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask;
use Dvsa\Olcs\Api\Domain\Command\Application\GrantGoods;
use Dvsa\Olcs\Api\Domain\Command\Application\GrantPsv;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant as GrantApplicationCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Lva\Application\GrantValidationService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\CreateFromGrant;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Dvsa\Olcs\Transfer\Command\Application\Grant as Cmd;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Grant Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantTest extends AbstractCommandHandlerTestCase
{
    public const SERVICE_TRANSACTION_MANAGER = 'TransactionManager';
    public const SERVICE_REPOSITORY_MANAGER = 'RepositoryServiceManager';
    public const SERVICE_VALIDATION = 'ApplicationGrantValidationService';
    public const SERVICE_QUERY_HANDLER = 'QueryHandlerManager';
    public const SERVICE_COMMAND_HANDLER = 'CommandHandlerManager';
    public const REPOSITORY_APPLICATION = 'Application';

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->sut = new GrantApplicationCommandHandler();
        $this->mockRepo('Application', Application::class);
        $this->mockedSmServices['ApplicationGrantValidationService'] = m::mock();
        parent::setUp();
    }

    public function testHandleCommandIsDefined(): void
    {
        $command = new GrantApplicationCommandHandler();
        $this->assertIsCallable($command->handleCommand(...));
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'shouldCreateInspectionRequest' => 'Y',
            'dueDate' => null
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithFailedValidation(): void
    {
        $data = [
            'shouldCreateInspectionRequest' => 'N',
            'dueDate' => null
        ];
        $command = Cmd::create($data);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn(['MESSAGE1', 'MESSAGE2']);

        try {
            $this->sut->handleCommand($command);

            $this->fail('Exception should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertSame(['MESSAGE1', 'MESSAGE2'], $e->getMessages());
        }
    }

    public function testHandleCommandGoods(): void
    {
        $data = [
            'shouldCreateInspectionRequest' => 'N',
            'dueDate' => null,
            'id' => 111,
            'notes' => null,
        ];

        $command = Cmd::create(array_merge($data, ['grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED]));

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('isGoods')
            ->andReturn(true);
        $application->shouldReceive('getTrafficArea->getId')
            ->andReturn('TA');
        $application->shouldReceive('setRequestInspection')
            ->with(false)
            ->once();
        $application->expects('getLicence->getId')->andReturn(222);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->times(2);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('GrantGoods');
        $this->expectedSideEffect(GrantGoods::class, $data, $result1);

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 111, 'trafficArea' => 'TA', 'publicationSection' => 4],
            new Result()
        );
        $this->expectedSideEffectAsSystemUser(
            CloseTexTask::class,
            ['id' => 111],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(222);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GrantGoods'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandPsv(): void
    {
        $data = [
            'shouldCreateInspectionRequest' => 'N',
            'dueDate' => null,
            'id' => 111,
            'notes' => null
        ];

        $command = Cmd::create(array_merge($data, ['grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED]));

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('isGoods')
            ->andReturn(false);
        $application->shouldReceive('getTrafficArea->getId')
            ->andReturn('TA');
        $application->expects('getLicence->getId')->andReturn(222);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('GrantPsv');
        $this->expectedSideEffect(GrantPsv::class, $data, $result1);

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 111, 'trafficArea' => 'TA', 'publicationSection' => 4],
            new Result()
        );
        $this->expectedSideEffectAsSystemUser(
            CloseTexTask::class,
            ['id' => 111],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(222);

        $this->repoMap['Application']->shouldReceive('save')->times(1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GrantPsv'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandPsvWithInspectionRequest(): void
    {
        $data = [
            'shouldCreateInspectionRequest' => 'Y',
            'dueDate' => 3,
            'id' => 111,
            'notes' => 'Notes go here'
        ];

        $command = Cmd::create(array_merge($data, ['grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED]));

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('isGoods')
            ->andReturn(false);
        $application->shouldReceive('getTrafficArea->getId')
            ->andReturn('TA');
        $application->expects('getLicence->getId')->andReturn(222);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('GrantPsv');
        $this->expectedSideEffect(GrantPsv::class, $data, $result1);

        $result2 = new Result();
        $result2->addMessage('CreateFromGrant');
        $data = [
            'application' => 111,
            'duePeriod' => 3,
            'caseworkerNotes' => 'Notes go here'
        ];
        $this->expectedSideEffect(CreateFromGrant::class, $data, $result2);

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 111, 'trafficArea' => 'TA'],
            new Result()
        );
        $this->expectedSideEffectAsSystemUser(
            CloseTexTask::class,
            ['id' => 111],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(222);

        $this->repoMap['Application']->shouldReceive('save')->times(1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GrantPsv',
                'CreateFromGrant'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandGoodsWithInspectionRequest(): void
    {
        $data = [
            'shouldCreateInspectionRequest' => 'Y',
            'dueDate' => 3,
            'id' => 111,
            'notes' => 'foo'
        ];

        $command = Cmd::create(array_merge($data, ['grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED]));

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);

        $application->shouldReceive('isGoods')
            ->andReturn(true);
        $application->shouldReceive('getTrafficArea->getId')
            ->andReturn('TA');
        $application->shouldReceive('setRequestInspection')
            ->with(true)
            ->once()
            ->shouldReceive('setRequestInspectionDelay')
            ->with(3)
            ->once()
            ->shouldReceive('setRequestInspectionComment')
            ->with('foo')
            ->once()
            ->getMock();
        $application->expects('getLicence->getId')->andReturn(222);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->times(2);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('GrantGoods');
        $this->expectedSideEffect(GrantGoods::class, $data, $result1);

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 111, 'trafficArea' => 'TA', 'publicationSection' => 4],
            new Result()
        );
        $this->expectedSideEffectAsSystemUser(
            CloseTexTask::class,
            ['id' => 111],
            new Result()
        );

        $this->expectedLicenceCacheClearSideEffect(222);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GrantGoods',
                'Inspection request details saved'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
