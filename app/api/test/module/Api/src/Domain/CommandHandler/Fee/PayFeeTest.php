<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as EndInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ValidateApplication;
use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\PayFee;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication as SubmitIrhpApplicationCmd;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptIrhpPermits;
use Dvsa\Olcs\Transfer\Command\Permits\CompleteIssuePayment;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Pay Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PayFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PayFee();
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockRepo('ContinuationDetail', \Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            Application::APPLICATION_STATUS_GRANTED,
            Application::INTERIM_STATUS_GRANTED,
            Application::INTERIM_STATUS_INFORCE,
        ];

        parent::initReferences();
    }

    public function testHandleCommandIrhpApplicationFeeNoSideEffect(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $irhpApplicationId = 10001;
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canBeSubmitted')->once()->andReturn(true);
        $irhpApplication->shouldReceive('hasOutstandingFees')->once()->andReturn(true);
        $irhpApplication->shouldReceive('getId')->andReturn($irhpApplicationId);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId'); //avoided breaking old code
        $fee->shouldReceive('getFeeType->isIrhpApplication')->withNoArgs()->andReturn(true);
        $fee->shouldReceive('getFeeType->isIrhpApplicationIssue')->withNoArgs()->andReturn(false);
        $fee->shouldReceive('getIrhpApplication')->withNoArgs()->andReturn($irhpApplication);
        $fee->shouldReceive('getApplication')->andReturnNull();
        $fee->shouldReceive('getTask')->andReturnNull();

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = new Result();
        $result->addMessage('message');
        $result->addId('returnedId', $irhpApplicationId);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandIrhpApplicationWithSideEffect(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $irhpApplicationId = 10001;
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canBeSubmitted')->once()->andReturn(true);
        $irhpApplication->shouldReceive('hasOutstandingFees')->once()->andReturn(false);
        $irhpApplication->shouldReceive('getId')->andReturn($irhpApplicationId);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->times(3);
        $fee->shouldReceive('getFeeType->isIrhpApplication')->withNoArgs()->andReturn(true);
        $fee->shouldReceive('getFeeType->isIrhpApplicationIssue')->withNoArgs()->andReturn(false);
        $fee->shouldReceive('getIrhpApplication')->withNoArgs()->andReturn($irhpApplication);
        $fee->shouldReceive('getApplication')->andReturnNull();
        $fee->shouldReceive('getTask')->andReturnNull();

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = new Result();
        $result->addMessage('message');
        $result->addId('returnedId', $irhpApplicationId);

        $this->expectedSideEffectAsSystemUser(SubmitIrhpApplicationCmd::class, ['id' => $irhpApplicationId], $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['returnedId' => $irhpApplicationId],
            'messages' => [0 => 'message']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandIrhpIssueNoSideEffect(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $irhpApplicationId = 10001;
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isAwaitingFee')->andReturn(false);
        $irhpApplication->shouldReceive('getId')->andReturn($irhpApplicationId);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId');
        $fee->shouldReceive('getFeeType->isIrhpApplication')->withNoArgs()->andReturn(false);
        $fee->shouldReceive('getFeeType->isIrhpApplicationIssue')->withNoArgs()->andReturn(true);
        $fee->shouldReceive('getIrhpApplication')->withNoArgs()->andReturn($irhpApplication);
        $fee->shouldReceive('getApplication')->withNoArgs()->andReturnNull();
        $fee->shouldReceive('getTask')->withNoArgs()->andReturnNull();

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = new Result();
        $result->addMessage('message');
        $result->addId('returnedId', $irhpApplicationId);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandIrhpIssueWithSideEffect(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $irhpApplicationId = 10001;
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isAwaitingFee')->andReturn(true);
        $irhpApplication->shouldReceive('getId')->andReturn($irhpApplicationId);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId');
        $fee->shouldReceive('getFeeType->isIrhpApplication')->withNoArgs()->andReturn(false);
        $fee->shouldReceive('getFeeType->isIrhpApplicationIssue')->withNoArgs()->andReturn(true);
        $fee->shouldReceive('getIrhpApplication')->withNoArgs()->andReturn($irhpApplication);
        $fee->shouldReceive('getApplication')->withNoArgs()->andReturnNull();
        $fee->shouldReceive('getTask')->withNoArgs()->andReturnNull();

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = new Result();
        $result->addMessage('message');
        $result->addId('returnedId', $irhpApplicationId);

        $this->expectedSideEffectAsSystemUser(
            AcceptIrhpPermits::class,
            ['id' => $irhpApplicationId],
            $result
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['returnedId' => $irhpApplicationId],
            'messages' => [0 => 'message']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutApplication(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('foo');

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithVariation(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(true);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('foo');

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithUnGrantedApplication(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_UNDER_CONSIDERATION]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('foo');

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithOutstandingApplicationFees(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_GRANTED]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('foo');

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee)
            ->shouldReceive('fetchOutstandingGrantFeesByApplicationId')
            ->once()
            ->with(222)
            ->andReturn(['foo']);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutOutstandingApplicationFees(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_GRANTED]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('foo');

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee)
            ->shouldReceive('fetchOutstandingGrantFeesByApplicationId')
            ->once()
            ->with(222)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('ValidateApplication');
        $this->expectedSideEffectAsSystemUser(ValidateApplication::class, ['id' => 222], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'ValidateApplication'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandContinuationNoContinuationDetail(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('CONT');
        $fee->shouldReceive('getLicence->getId')->andReturn(717);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->andReturn($fee);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchOngoingForLicence')->with(717)
            ->andThrow(\Doctrine\ORM\UnexpectedResultException::class);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public static function licenceStatusNotAllowed(): array
    {
        return [
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_GRANTED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_REFUSED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_REVOKED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SURRENDERED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_TERMINATED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_WITHDRAWN],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceStatusNotAllowed')]
    public function testHandleCommandContinuationNotAllowedLicenceStatus(mixed $status): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('CONT');
        $fee->shouldReceive('getLicence->getId')->andReturn(717);
        $fee->shouldReceive('getLicence->getStatus->getId')->andReturn($status);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->andReturn($fee);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchOngoingForLicence')->with(717)
            ->andReturn('FOO');

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutOutstandingApplicationFeesWithGrantIntWithoutInterimStatus(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_GRANTED]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn(FeeType::FEE_TYPE_GRANT);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee)
            ->shouldReceive('fetchOutstandingGrantFeesByApplicationId')
            ->once()
            ->with(222)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('ValidateApplication');
        $this->expectedSideEffectAsSystemUser(ValidateApplication::class, ['id' => 222], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'ValidateApplication'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function licenceStatusAllowed(): array
    {
        return [
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CURTAILED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED],
            [\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_VALID],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceStatusAllowed')]
    public function testHandleCommandContinuationHasOutstandingFees(mixed $status): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('CONT');
        $fee->shouldReceive('getLicence->getId')->andReturn(717);
        $fee->shouldReceive('getLicence->getStatus->getId')->andReturn($status);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->andReturn($fee);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchOngoingForLicence')->with(717)
            ->andReturn('FOO');

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->with(717)
            ->andReturn(['SOME']);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceStatusAllowed')]
    public function testHandleCommandContinuation(mixed $status): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('CONT');
        $fee->shouldReceive('getLicence->getId')->andReturn(717);
        $fee->shouldReceive('getLicence->getStatus->getId')->andReturn($status);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->andReturn($fee);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchOngoingForLicence')->with(717)
            ->andReturn('FOO');

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->with(717)
            ->andReturn([]);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence::class,
            ['id' => 717, 'version' => null],
            (new Result())->addMessage('XXX')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['XXX'],
            'flags' => [ContinuationDetailEntity::RESULT_LICENCE_CONTINUED => true]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutOutstandingApplicationFeesWithGrantInt(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_GRANTED]);
        $application->setInterimStatus($this->refData[Application::INTERIM_STATUS_GRANTED]);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn(FeeType::FEE_TYPE_GRANT);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee)
            ->shouldReceive('fetchOutstandingGrantFeesByApplicationId')
            ->once()
            ->with(222)
            ->andReturn([]);

        $result1 = new Result();
        $result1->addMessage('ValidateApplication');
        $this->expectedSideEffectAsSystemUser(ValidateApplication::class, ['id' => 222], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'ValidateApplication',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithVariationAndInterimInForce(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $this->setupIsInternalUser(false);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_UNDER_CONSIDERATION]);
        $application->setInterimStatus($this->refData[Application::INTERIM_STATUS_INFORCE]);

        $application
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->twice()
            ->getMock();
        $endResult = new Result();
        $endResult->addMessage('EndInterim');
        $this->expectedSideEffect(EndInterimCmd::class, ['id' => 222], $endResult);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn(FeeType::FEE_TYPE_GRANT);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'EndInterim'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCancelApplicationTasks(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $this->setupIsInternalUser(true);

        /* @var $application Application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_UNDER_CONSIDERATION]);
        $application->setInterimStatus($this->refData[Application::INTERIM_STATUS_INFORCE]);
        $application->setInterimStatus($this->refData[Application::INTERIM_STATUS_INFORCE]);

        $application->shouldReceive('isGoods')->twice()->andReturn(true);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn(FeeType::FEE_TYPE_GRANT);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($fee);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 222],
            (new Result())->addMessage('CLOSE_TEX_TASK')
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask::class,
            ['id' => 222],
            (new Result())->addMessage('CLOSE_FEEDUE_TASK')
        );

        $this->expectedSideEffect(
            EndInterimCmd::class,
            ['id' => 222],
            (new Result())->addMessage('EndInterim')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'EndInterim',
                'CLOSE_TEX_TASK',
                'CLOSE_FEEDUE_TASK',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithFeeTask(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        /** @var Task $task */
        $task = m::mock(Task::class)->makePartial();
        $task->setId(222);

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setTask($task);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn(FeeType::FEE_TYPE_APP);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($fee);

        $this->expectedSideEffect(
            CloseTasksCmd::class,
            ['ids' => [222]],
            (new Result())->addMessage('TASK_CLOSED')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'TASK_CLOSED',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithGrantInterimFee(): void
    {
        $command = PayFeeCommand::create(['id' => 111]);

        $this->setupIsInternalUser(false);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(222);
        $application->setIsVariation(false);
        $application->setStatus($this->refData[Application::APPLICATION_STATUS_UNDER_CONSIDERATION]);
        $application->setInterimStatus($this->refData[Application::INTERIM_STATUS_GRANTED]);

        $application
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        /** @var FeeEntity $fee */
        $fee = m::mock(FeeEntity::class)->makePartial();
        $fee->setApplication($application);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn(FeeType::FEE_TYPE_GRANTINT);

        $this->repoMap['Fee']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($fee);

        $this->expectedSideEffect(
            InForceInterim::class,
            ['id' => 222],
            (new Result())->addMessage('IN-FORCE-INTERIM')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'IN-FORCE-INTERIM'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
