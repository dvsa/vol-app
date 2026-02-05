<?php

declare(strict_types=1);

/**
 * ByLicence Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ByLicence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence as Qry;
use Mockery as m;

/**
 * ByLicence Test
 */
class ByLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByLicence();
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 7;

        $query = Qry::create(['licence' => $licenceId]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $licence = m::mock();
        $organisation = m::mock();
        $organisation->shouldReceive('serialize')->once()->andReturn('foo');

        $licence
            ->shouldReceive('serialize')
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($licence);

        $this->repoMap['Cases']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Cases']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
