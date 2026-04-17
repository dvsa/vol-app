<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Surrender;
use Doctrine\ORM\QueryBuilder;

class SurrenderTest extends RepositoryTestCase
{
    /** @var Surrender */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Surrender::class);
    }

    public function testFetchByLicenceId(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb('{QUERY}');

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn(['Result']);

        $this->mockCreateQueryBuilder($qb);

        $this->sut->fetchByLicenceId($licenceId);

        $expectedQuery = '{QUERY} AND m.licence = ' . $licenceId;

        self::assertEquals($expectedQuery, $this->query);
    }
}
