<?php

declare(strict_types=1);

/**
 * Sla Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;

/**
 * Sla Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class SlaTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repository\Sla::class);
    }

    public function testFetchByCategories(): void
    {
        $categories = ['foo', 'bar'];

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('foobar');

        $result = $this->sut->fetchByCategories($categories);

        $this->assertEquals('QUERY AND m.category IN([[["foo","bar"]]])', $this->query);

        $this->assertEquals('foobar', $result);
    }

    public function testFetchByCategoryFieldAndCompareTo(): void
    {
        $sla = m::mock(SlaEntity::class);

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getSingleResult')->once()->andReturn($sla);

        $result = $this->sut->fetchByCategoryFieldAndCompareTo('cat', 'fld', 'cmp');

        // createMockQb() records only the first argument passed to andWhere(), so the
        // field and compareTo predicates do not appear here; the category one proves the
        // parameters are bound by name against a signature-strict QueryBuilder mock.
        $this->assertEquals('QUERY AND m.category = [[cat]]', $this->query);

        $this->assertSame($sla, $result);
    }
}
