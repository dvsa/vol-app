<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive as Qry;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SystemInfoMessage::class)]
class SystemInfoMessageTest extends RepositoryTestCase
{
    /** @var  Repository\SystemInfoMessage */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\SystemInfoMessage::class);
    }

    public function testListActive(): void
    {
        $expect = ['RESULTS'];

        $isInternal = true;
        $qry = Qry::create(['isInternal' => $isInternal]);

        $qb = $this->createMockQb('{QUERY}');
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn($expect);

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->with()->andReturnSelf();

        self::assertEquals($expect, $this->sut->fetchListActive($qry));

        $now = (new DateTime())->format(DateTime::ATOM);

        $expectedQuery = '{QUERY} ' .
            'SELECT partial m.{id, description} ' .
            'AND m.isInternal = [[1]] ' .
            'AND m.startDate <= [[' . $now . ']] ' .
            'AND m.endDate >= [[' . $now . ']]';

        self::assertEquals($expectedQuery, $this->query);
    }
}
