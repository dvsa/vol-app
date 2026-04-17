<?php

declare(strict_types=1);

/**
 * By Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\ById;

/**
 * By Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ByIdTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        $this->sut = new ById();

        parent::setUp();
    }

    public function testModifyQuery(): void
    {
        $id = 111;

        $this->qb->shouldReceive('getRootAliases')
            ->andReturn(['a'])
            ->shouldReceive('andWhere')
            ->once()
            ->with('a.id = :byId')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->once()
            ->with('byId', 111)
            ->andReturnSelf()
            ->shouldReceive('setMaxResults')
            ->with(1)
            ->andReturnSelf();

        $this->qb->shouldReceive('expr->eq')
            ->with('a.id', ':byId')
            ->andReturn('a.id = :byId');

        $this->sut->modifyQuery($this->qb, [$id]);
    }
}
