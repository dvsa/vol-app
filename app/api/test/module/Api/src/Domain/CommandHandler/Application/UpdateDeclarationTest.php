<?php

declare(strict_types=1);

/**
 * UpdateDeclarationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateDeclaration;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateDeclaration as UpdateDeclarationCmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\CancelAllInterimFees as CancelAllInterimFeesCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as CreateFeeCommand;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;

/**
 * UpdateDeclarationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateDeclarationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateDeclaration();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('Fee', \Dvsa\Olcs\Api\Domain\Repository\Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'int_sts_requested',
            'sig_physical_signature',
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoInterimParam(): void
    {
        // Params
        $command = UpdateDeclarationCmd::create(
            [
                'id' => 627,
                'version' => 45,
                'declarationConfirmation' => 'Y',
                'signatureType' => 'sig_physical_signature'
            ]
        );

        // Mocks
        $application = $this->getApplication($command);
        $application->setInterimStatus('STATUS');
        $application->setInterimReason('SOME REASON');

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 45)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'undertakings'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['application' => 627],
            'messages' => ['Update declaration successful']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getDeclarationConfirmation());
        $this->assertEquals('STATUS', $application->getInterimStatus());
        $this->assertEquals('SOME REASON', $application->getInterimReason());
        $this->assertEquals('sig_physical_signature', $application->getSignatureType());
    }

    public function testHandleCommandInterimNo(): void
    {
        // Params
        $command = UpdateDeclarationCmd::create(
            [
                'id' => 627,
                'version' => 45,
                'declarationConfirmation' => 'Y',
                'interimRequested' => 'N',
                'interimReason' => 'SOME REASON',
            ]
        );

        // Mocks
        $application = $this->getApplication($command);

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 45)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->expectedSideEffect(
            CancelAllInterimFeesCommand::class,
            ['id' => 627],
            new Result()
        );
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'undertakings'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['application' => 627],
            'messages' => ['Update declaration successful']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getDeclarationConfirmation());
        $this->assertEquals(null, $application->getInterimStatus());
        $this->assertEquals(null, $application->getInterimReason());
    }

    public function testHandleCommandInterimYesExistingFee(): void
    {
        // Params
        $command = UpdateDeclarationCmd::create(
            [
                'id' => 627,
                'version' => 45,
                'declarationConfirmation' => 'Y',
                'interimRequested' => 'Y',
                'interimReason' => 'SOME REASON',
            ]
        );

        // Mocks
        $application = $this->getApplication($command);

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 45)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Fee']
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(627, true, true)
            ->once()
            ->andReturn(['SOMETHING'])
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeTypeEntity::FEE_TYPE_VAR, 627)
            ->andReturn('foo')
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'undertakings'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['application' => 627],
            'messages' => ['Update declaration successful']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getDeclarationConfirmation());
        $this->assertEquals($this->mapRefData('int_sts_requested'), $application->getInterimStatus());
        $this->assertEquals('SOME REASON', $application->getInterimReason());
    }

    public function testHandleCommandInterimYesExistingFeeVar(): void
    {
        // Params
        $command = UpdateDeclarationCmd::create(
            [
                'id' => 627,
                'version' => 45,
                'declarationConfirmation' => 'Y',
                'interimRequested' => 'Y',
                'interimReason' => 'SOME REASON',
            ]
        );

        // Mocks
        $application = $this->getApplication($command);

        // Expectations
        $application->shouldReceive('isVariation')
            ->once()
            ->andReturn(true)
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 45)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Fee']
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(627, true, true)
            ->once()
            ->andReturn()
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeTypeEntity::FEE_TYPE_VAR, 627)
            ->andReturn('foo')
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            CreateFeeCommand::class,
            ['id' => 627, 'feeTypeFeeType' => 'GRANTINT', 'task' => null],
            new Result()
        );
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'undertakings'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['application' => 627],
            'messages' => ['Update declaration successful']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getDeclarationConfirmation());
        $this->assertEquals($this->mapRefData('int_sts_requested'), $application->getInterimStatus());
        $this->assertEquals('SOME REASON', $application->getInterimReason());
    }

    public function testHandleCommandInterimYesNoFee(): void
    {
        // Params
        $command = UpdateDeclarationCmd::create(
            [
                'id' => 627,
                'version' => 45,
                'declarationConfirmation' => 'Y',
                'interimRequested' => 'Y',
                'interimReason' => 'SOME REASON',
            ]
        );

        // Mocks
        $application = $this->getApplication($command);

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 45)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')->with(627, true, true)->once()
            ->andReturn([]);

        $this->expectedSideEffect(
            CreateFeeCommand::class,
            ['id' => 627, 'feeTypeFeeType' => 'GRANTINT', 'task' => null],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 627, 'section' => 'undertakings'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['application' => 627],
            'messages' => ['Update declaration successful']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getDeclarationConfirmation());
        $this->assertEquals($this->mapRefData('int_sts_requested'), $application->getInterimStatus());
        $this->assertEquals('SOME REASON', $application->getInterimReason());
    }

    protected function getApplication(mixed $command): mixed
    {
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId($command->getId());

        return $application;
    }
}
