<?php

declare(strict_types=1);

/**
 * Create Overpayment Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateOverpaymentFee as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\CreateOverpaymentFee;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Overpayment Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateOverpaymentFeeTest extends AbstractCommandHandlerTestCase
{
    protected $mockFeesHelperService;

    public function setUp(): void
    {
        $this->mockFeesHelperService = m::mock(FeesHelper::class);
        $this->mockedSmServices = [
            'FeesHelperService' => $this->mockFeesHelperService,
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
        ];

        $this->sut = new CreateOverpaymentFee();
        $this->mockRepo('FeeType', Repository\FeeType::class);

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
            FeeTypeEntity::class => [
                99 => m::mock(FeeTypeEntity::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $fee1 = $this->mapReference(FeeEntity::class, 100);
        $fee2 = $this->mapReference(FeeEntity::class, 101);
        $now = new DateTime();

        $data = [
            'receivedAmount' => '100.00',
            'fees' => [$fee1, $fee2],
        ];

        $command = Cmd::create($data);

        $this->mockFeesHelperService
            ->shouldReceive('getOverpaymentAmount')
            ->once()
            ->with('100.00', [$fee1, $fee2])
            ->andReturn('50.00');

        $this->mockFeesHelperService
            ->shouldReceive('sortFeesByInvoiceDate')
            ->once()
            ->with([$fee1, $fee2])
            ->andReturn([$fee2, $fee1]);

        $this->mockFeesHelperService
            ->shouldReceive('getIdsFromFee')
            ->once()
            ->with($fee2)
            ->andReturn(['application' => '9', 'licence' => '7']);

        $this->repoMap['FeeType']
            ->shouldReceive('fetchLatestForOverpayment')
            ->andReturn($this->mapReference(FeeTypeEntity::class, 99));

        $expectedDtoData = [
            'amount'       => '50.00',
            'invoicedDate' => $now->format(\DateTime::W3C),
            'feeType'      => 99,
            'description'  => 'Overpayment on fees: 101, 100',
            'application'  => '9',
            'licence'      => '7',
        ];

        $createResult = new Result();
        $createResult
            ->addMessage('Fee created')
            ->addId('fee', 102);
        $this->expectedSideEffect(CreateFeeCmd::class, $expectedDtoData, $createResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 102,
            ],
            'messages' => [
                'Fee created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
