<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\LicenceStatusRule\LicenceStatusRule
 */
class LicenceStatusRuleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\LicenceStatusRule\LicenceStatusRule();
        $this->mockRepo('LicenceStatusRule', Repository\LicenceStatusRule::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\LicenceStatusRule\LicenceStatusRule::create([]);

        $mockEntity = m::mock(QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->with(
                [
                    'licence' => [
                        'decisions',
                    ],
                    'licenceStatus',
                ]
            )
            ->andReturn(['unit_Result'])
            ->getMock();

        $this->repoMap['LicenceStatusRule']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEntity);

        /** @var QueryHandler\ResultList $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertSame(['unit_Result'], $actual->serialize());
    }
}
