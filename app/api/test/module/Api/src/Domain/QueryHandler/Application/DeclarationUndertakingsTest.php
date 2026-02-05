<?php

declare(strict_types=1);

/**
 * Declaration Undertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\DeclarationUndertakings;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Application\DeclarationUndertakings as Qry;
use Mockery as m;

/**
 * Declaration Undertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeclarationUndertakingsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeclarationUndertakings();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices['Review\ApplicationUndertakings'] = m::mock();

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $mockApplication->shouldReceive('isGoods')->andReturn(true);

        $data = [
            'foo' => 'bar',
            'isGoods' => true,
            'isInternal' => false
        ];

        $this->mockedSmServices['Review\ApplicationUndertakings']->shouldReceive('getMarkup')
            ->once()->with($data)->andReturn('markup');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication);

        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $expected = [
            'foo' => 'bar',
            'undertakings' => 'markup'
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }
}
