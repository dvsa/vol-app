<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateGrantFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\GrantGoods;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\GrantGoods as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CancelAllInterimFees;

/**
 * Grant Goods Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantGoodsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantGoods();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_GRANTED,
            ApplicationEntity::APPLICATION_STATUS_GRANTED
        ];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $this->setupIsInternalUser(false);

        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('getCurrentInterimStatus')->once()->andReturn(ApplicationEntity::INTERIM_STATUS_GRANTED);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateGrantFee');
        $this->expectedSideEffectAsSystemUser(CreateGrantFee::class, ['id' => 111], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application status updated',
                'Licence status updated',
                'CreateGrantFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(Licence::LICENCE_STATUS_GRANTED, $licence->getStatus()->getId());
        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_GRANTED, $application->getStatus()->getId());
    }

    public function testHandleCommandCloseTasks(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        $this->setupIsInternalUser();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setIsVariation(true);
        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application)
            ->shouldReceive('save')->with($application)->once();

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 111],
            (new Result())->addMessage('CLOSE_TEX_TASK')
        );
        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask::class,
            ['id' => 111],
            (new Result())->addMessage('CLOSE_FEEDUE_TASK')
        );

        $result1 = new Result();
        $result1->addMessage('CreateGrantFee');
        $this->expectedSideEffect(CreateGrantFee::class, ['id' => 111], $result1);

        $result2 = new Result();
        $result2->addMessage('CancelAllInterimFees');
        $this->expectedSideEffectAsSystemUser(CancelAllInterimFees::class, ['id' => 111], $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application status updated',
                'Licence status updated',
                'CLOSE_TEX_TASK',
                'CLOSE_FEEDUE_TASK',
                'CancelAllInterimFees',
                'CreateGrantFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandRefund(): void
    {
        $this->setupIsInternalUser(false);

        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('getCurrentInterimStatus')->once()->andReturn(ApplicationEntity::INTERIM_STATUS_REQUESTED);

        $feeEntity = m::mock(Fee::class);
        $feeEntity->shouldReceive('getFeeType')->andReturn(
            m::mock(FeeType::class)->shouldReceive('isInterimGrantFee')
                ->once()
                ->andReturnTrue()
                ->getMock()
        );
        $feeEntity->shouldReceive('canRefund')->andReturnTrue();
        $feeEntity->shouldReceive('getId')->andReturn(1);

        $application->setFees(new ArrayCollection([$feeEntity]));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateGrantFee');
        $this->expectedSideEffectAsSystemUser(
            CreateGrantFee::class,
            ['id' => 111],
            $result1
        );
        $this->expectedSideEffect(
            Create::class,
            [
                'entityId' => $feeEntity->getId(),
                'type' => Queue::TYPE_REFUND_INTERIM_FEES,
                'status' => Queue::STATUS_QUEUED,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateFeeStatus::class,
            [
                'id' => $feeEntity->getId(),
                'status' => Fee::STATUS_REFUND_PENDING
            ],
            new Result()
        );

        $this->sut->handleCommand($command);
    }
}
