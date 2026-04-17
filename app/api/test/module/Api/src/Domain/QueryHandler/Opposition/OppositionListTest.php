<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Opposition;

use Dvsa\Olcs\Api\Domain\QueryHandler\Opposition\OppositionList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as OppositionRepo;
use Dvsa\Olcs\Transfer\Query\Opposition\OppositionList as Qry;
use Mockery as m;

/**
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Opposition\OppositionList::class)]
class OppositionListTest extends QueryHandlerTestCase
{
    /** @var  OppositionList */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new OppositionList();
        $this->mockRepo('Opposition', OppositionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->times(2)->andReturn('foo');

        $this->repoMap['Opposition']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult, clone $mockResult]);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo', 'foo']);
    }
}
