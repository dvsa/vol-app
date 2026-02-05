<?php

declare(strict_types=1);

/**
 * Transaction by reference Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Transaction;

use Dvsa\Olcs\Api\Domain\QueryHandler\Transaction\TransactionByReference as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Transaction as PaymentRepo;
use Dvsa\Olcs\Transfer\Query\Transaction\TransactionByReference as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Transaction by reference Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionByReferenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Transaction', PaymentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $paymentRef = 'OLCS-1234-ABCD';

        $query = Qry::create(['reference' => $paymentRef]);

        $mockPayment = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Transaction']
            ->shouldReceive('fetchByReference')
            ->with($paymentRef)
            ->once()
            ->andReturn($mockPayment);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);
    }
}
