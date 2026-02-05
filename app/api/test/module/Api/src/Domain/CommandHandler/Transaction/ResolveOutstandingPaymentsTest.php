<?php

declare(strict_types=1);

/**
 * Resolve Outstanding Payments Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolveOutstandingPayments as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as ResolvePaymentCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Transaction\ResolveOutstandingPayments;
use Dvsa\Olcs\Api\Domain\Repository\AbstractRepository as Repo;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as PaymentEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Logging\Log\Logger;

/**
* Resolve Outstanding Payments Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ResolveOutstandingPaymentsTest extends AbstractCommandHandlerTestCase
{
    protected $mockCpmsService;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
        ];

        $this->sut = new ResolveOutstandingPayments();
        $this->mockRepo('Transaction', Repo::class);
        $this->mockRepo('SystemParameter', Repo::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->references = [
            PaymentEntity::class => [
                99 => m::mock(PaymentEntity::class)->makePartial(),
                100 => m::mock(PaymentEntity::class)->makePartial(),
            ],
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        // expectations
        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->once()
            ->with('RESOLVE_CARD_PAYMENTS_MIN_AGE')
            ->andReturn('30');

        $transactions = new ArrayCollection(
            [
                $this->mapReference(PaymentEntity::class, 99),
                $this->mapReference(PaymentEntity::class, 100),
            ]
        );

        $this->repoMap['Transaction']
            ->shouldReceive('fetchOutstandingCardPayments')
            ->once()
            ->with('30')
            ->andReturn($transactions);

        $this->expectedSideEffect(
            ResolvePaymentCmd::class,
            ['id' => 99],
            (new Result())->addMessage('Transaction 99 resolved as Failed')
        );
        $this->expectedSideEffect(
            ResolvePaymentCmd::class,
            ['id' => 100],
            (new Result())->addMessage('Transaction 100 resolved as Failed')
        );

        $command = Cmd::create([]);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Transaction 99 resolved as Failed',
                'Transaction 100 resolved as Failed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithException(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        // expectations
        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->once()
            ->with('RESOLVE_CARD_PAYMENTS_MIN_AGE')
            ->andReturn('30');

        $transactions = new ArrayCollection(
            [
                $this->mapReference(PaymentEntity::class, 99),
                $this->mapReference(PaymentEntity::class, 100),
            ]
        );

        $this->repoMap['Transaction']
            ->shouldReceive('fetchOutstandingCardPayments')
            ->once()
            ->with('30')
            ->andReturn($transactions);

        $this->expectedSideEffectThrowsException(
            ResolvePaymentCmd::class,
            ['id' => 99],
            (new \Exception('Foo'))
        );

        $this->expectedSideEffect(
            ResolvePaymentCmd::class,
            ['id' => 100],
            (new Result())->addMessage('Transaction 100 resolved as Failed')
        );

        $result = $this->sut->handleCommand(Cmd::create([]));
        $expected = [
            'id' => [],
            'messages' => [
                'Error resolving payment for transaction 99: Foo',
                'Transaction 100 resolved as Failed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
