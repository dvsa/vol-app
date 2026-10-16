<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as Entity;
use Dvsa\Olcs\Transfer\Query\Cases\Si\SiList as SiListQry;

final class SeriousInfringementTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(SiRepo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn('RESULTS');

        $this->assertSame('RESULTS', $this->sut->fetchList(SiListQry::create(['case' => 812])));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.case = :case',
            $qb->getDQL(),
        );
        $this->assertSame(812, $qb->getParameter('case')->getValue());
    }
}
