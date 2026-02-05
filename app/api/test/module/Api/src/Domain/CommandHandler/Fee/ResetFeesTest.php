<?php

declare(strict_types=1);

/**
 * Reset Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\ResetFees as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\ResetFees;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Reset Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ResetFeesTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var (\Dvsa\OlcsTest\Api\Domain\CommandHandler\Trailer\FeesHelper & \Mockery\MockInterface)
     */
    public $mockFeesHelperService;
    public function setUp(): void
    {
        $this->mockFeesHelperService = m::mock(FeesHelper::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
        ];

        $this->sut = new ResetFees();
        $this->mockRepo('Fee', Repository\FeeType::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            FeeEntity::class => [
                100 => m::mock(FeeEntity::class),
                101 => m::mock(FeeEntity::class),
            ],
        ];

        $this->refData = [
            FeeEntity::STATUS_OUTSTANDING => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('Outstanding')
                ->getMock(),
            FeeEntity::STATUS_CANCELLED => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('Cancelled')
                ->getMock(),
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $fee1 = $this->mapReference(FeeEntity::class, 100);
        $fee2 = $this->mapReference(FeeEntity::class, 101);
        $data = [
            'fees' => [$fee1, $fee2],
        ];

        $fee1->shouldReceive('isBalancingFee')->andReturn(false);
        $fee2->shouldReceive('isBalancingFee')->andReturn(true);

        $command = Cmd::create($data);

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee1)
            ->shouldReceive('save')
            ->once()
            ->with($fee2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Fee 100 reset to Outstanding',
                'Fee 101 reset to Cancelled',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
