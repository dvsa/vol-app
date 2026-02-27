<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Query Partial Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueryPartialTestCase extends MockeryTestCase
{
    /**
     * @var QueryPartialInterface
     */
    protected $sut;

    /**
     * @var m\MockInterface|QueryBuilder
     */
    protected $qb;

    public function setUp(): void
    {
        $this->qb = m::mock(QueryBuilder::class)->makePartial();

        $this->qb->select('a')
            ->from('foo', 'a');
    }
}
