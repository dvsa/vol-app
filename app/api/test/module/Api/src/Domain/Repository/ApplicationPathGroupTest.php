<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup as Entity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList;

final class ApplicationPathGroupTest extends RepositoryTestCase
{
    public function testFetchListForApplicationPathGroupList(): void
    {
        $this->setUpRealSut(ApplicationPathGroup::class, true);

        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(ApplicationPathGroupList::create([])));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.isVisibleInInternal = :isVisibleInInternal',
            $qb->getDQL(),
        );
        $this->assertTrue($qb->getParameter('isVisibleInInternal')->getValue());
    }
}
