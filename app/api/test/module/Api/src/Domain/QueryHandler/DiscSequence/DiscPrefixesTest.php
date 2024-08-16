<?php

/**
 * Disc Prefixes Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DiscSequence;

use Dvsa\Olcs\Api\Domain\QueryHandler\DiscSequence\DiscPrefixes as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Transfer\Query\DiscSequence\DiscPrefixes as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Disc Prefixes Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscPrefixesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('DiscSequence', DiscSequenceRepo::class);

        parent::setUp();
    }

    /**
     * @dataProvider emptyParamsProvider
     */
    public function testHandleQueryEmpty($params)
    {
        $query = Qry::create($params);
        $this->assertEquals(['result' => [], 'count' => 0], $this->sut->handleQuery($query));
    }

    public function emptyParamsProvider()
    {
        return [
            [['niFlag' => 'N', 'licenceType' => 'ltyp_r']],
            [['niFlag' => 'Y', 'operatorType' => 'lcat_gv']]
        ];
    }

    public function testHandleQuery()
    {
        $niFlag = 'N';
        $operatorType = 'lcat_gv';
        $licenceType = 'ltyp_r';

        $query = Qry::create(
            ['niFlag' => $niFlag, 'operatorType' => $operatorType, 'licenceType' => $licenceType]
        );

        $mockDiscSequence = m::mock()
            ->shouldReceive('getDiscPrefix')
            ->with($licenceType)
            ->andReturn('AB')
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchDiscPrefixes')
            ->with($niFlag, $operatorType)
            ->once()
            ->andReturn([$mockDiscSequence]);

        $this->assertEquals(['result' => [1 => 'AB'], 'count' => 1], $this->sut->handleQuery($query));
    }
}
