<?php

declare(strict_types=1);

/**
 * Fee List Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\FeeList as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\FeesHelperService;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Fee List Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Fee', Repository\Fee::class);
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Application', Repository\Application::class);

        $this->mockedSmServices['FeesHelperService'] = m::mock(FeesHelperService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $fee1 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 1])->getMock();
        $fee2 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 2])->getMock();
        $mockList = new \ArrayObject([$fee1, $fee2]);

        $this->repoMap['Fee']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockList)
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2)
            ->shouldReceive('hasRows')
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getMinPaymentForFees')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('123.45')
            ->shouldReceive('getTotalOutstanding')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('200.00');

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'count' => 2,
            'count-unfiltered' => 1,
            'allowFeePayments' => true,
            'minPayment' => '123.45',
            'totalOutstanding' => '200.00',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleQueryWithLicence(): void
    {
        $licenceId = 7;
        $query = Qry::create(['licence' => $licenceId]);

        $fee1 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 1])->getMock();
        $fee2 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 2])->getMock();
        $mockList = new \ArrayObject([$fee1, $fee2]);

        $this->repoMap['Fee']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockList)
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2)
            ->shouldReceive('hasRows')
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->once()
            ->with($licenceId)
            ->andReturn(
                m::mock(Licence::class)
                    ->shouldReceive('allowFeePayments')
                    ->andReturn(false)
                    ->getMock()
            );

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getMinPaymentForFees')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('123.45')
            ->shouldReceive('getTotalOutstanding')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('200.00');

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'count' => 2,
            'count-unfiltered' => 1,
            'allowFeePayments' => false,
            'minPayment' => '123.45',
            'totalOutstanding' => '200.00',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleQueryWithApplication(): void
    {
        $applicationId = 7;
        $query = Qry::create(['application' => $applicationId]);

        $fee1 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 1])->getMock();
        $fee2 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 2])->getMock();
        $mockList = new \ArrayObject([$fee1, $fee2]);

        $this->repoMap['Fee']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockList)
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2)
            ->shouldReceive('hasRows')
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->once()
            ->with($applicationId)
            ->andReturn(
                m::mock(Application::class)
                    ->shouldReceive('allowFeePayments')
                    ->andReturn(false)
                    ->getMock()
            );

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getMinPaymentForFees')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('123.45')
            ->shouldReceive('getTotalOutstanding')
            ->with([$fee1, $fee2])
            ->once()
            ->andReturn('200.00');

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'count' => 2,
            'count-unfiltered' => 1,
            'allowFeePayments' => false,
            'minPayment' => '123.45',
            'totalOutstanding' => '200.00',
        ];

        $this->assertEquals($expected, $result);
    }
}
