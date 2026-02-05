<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateTexTask;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * CreateTexTaskTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTexTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTexTask();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        $this->references = [
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithOooDate(): void
    {
        $command = \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(['id' => 32]);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $application->setId(32);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $licence->setId(426);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 32],
            new Result()
        );

        $application->shouldReceive('getOutOfOppositionDate')->with()->once()->andReturn(new \DateTime('2015-09-10'));

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => \Dvsa\Olcs\Api\Entity\System\Category::CATEGORY_APPLICATION,
                'subCategory' => \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED,
                'description' => 'OOO Time Expired',
                'licence' => 426,
                'application' => 32,
                'actionDate' => (new \DateTime('2015-09-10'))->format(\DateTime::W3C),
            ],
            (new Result())->addMessage('RESULT')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['RESULT'], $result->getMessages());
    }

    public function testHandleCommandWithoutOooDate(): void
    {
        $command = \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(['id' => 32]);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $application->setId(32);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $licence->setId(426);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 32],
            new Result()
        );

        $application->shouldReceive('getOutOfOppositionDate')->with()->once()->andReturn('FOO');

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => \Dvsa\Olcs\Api\Entity\System\Category::CATEGORY_APPLICATION,
                'subCategory' => \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED,
                'description' => 'OOO Time Expired',
                'licence' => 426,
                'application' => 32,
                'actionDate' => (new DateTime())->format(\DateTime::W3C),
            ],
            (new Result())->addMessage('RESULT')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['RESULT'], $result->getMessages());
    }

    public function testHandleCommandInternal(): void
    {
        $command = \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(['id' => 32]);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $application->setId(32);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $licence->setId(426);
        $application->setLicence($licence);

        $identity = m::mock();

        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $user->setId(53);
        $team = new \Dvsa\Olcs\Api\Entity\User\Team();
        $team->setId(55);
        $user->setTeam($team);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 32],
            new Result()
        );

        $application->shouldReceive('getOutOfOppositionDate')->with()->once()->andReturn('FOO');

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')->once()
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(true);
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')->once()->with()->andReturn($identity);
        $identity->shouldReceive('getUser')->with()->once()->andReturn($user);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::class,
            [
                'category' => \Dvsa\Olcs\Api\Entity\System\Category::CATEGORY_APPLICATION,
                'subCategory' => \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED,
                'description' => 'OOO Time Expired',
                'licence' => 426,
                'application' => 32,
                'actionDate' => (new DateTime())->format(\DateTime::W3C),
                'assignedToUser' => 53,
                'assignedToTeam' => 55,
            ],
            (new Result())->addMessage('RESULT')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['RESULT'], $result->getMessages());
    }
}
