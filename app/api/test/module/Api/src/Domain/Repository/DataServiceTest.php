<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DataService::class)]
class DataServiceTest extends RepositoryTestCase
{
    public const ORG_ID = 9001;

    /** @var Repository\DataService | m\MockInterface  */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\DataService::class);
    }

    public function testFetchByOrgAndStatusForActiveLicences(): void
    {
        $qb = $this->createMockQb('{{QUERY}}');
        $qb->shouldReceive('getQuery->execute')->andReturn('EXPECT');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $query = TransferQry\Application\GetList::create(
            [
                'organisation' => self::ORG_ID,
            ]
        );

        $actual = $this->sut->fetchApplicationStatus($query);

        static::assertEquals('EXPECT', $actual);

        static::assertEquals(
            '{{QUERY}}' .
            ' INNER JOIN ' . Entity\Application\Application::class . ' a WITH a.status = m.id' .
            ' INNER JOIN a.licence l WITH l.organisation = [[' . self::ORG_ID . ']]',
            $this->query
        );
    }
}
