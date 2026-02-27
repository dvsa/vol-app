<?php

declare(strict_types=1);

/**
 * Trailers Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Trailers;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Licence\Trailers as Qry;

/**
 * Trailers Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TrailersTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Trailers();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Trailer', Repository\Trailer::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [
            'id' => 111
        ];

        $query = Qry::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('serialize')
            ->with(['organisation'])
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['Trailer']->shouldReceive('fetchList')
            ->with($query)
            ->andReturn('RESULTS')
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'trailers' => [
                'results' => 'RESULTS',
                'count' => 'COUNT'
            ]
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
