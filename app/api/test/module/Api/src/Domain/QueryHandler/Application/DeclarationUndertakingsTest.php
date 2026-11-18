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
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationUndertakingsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationUndertakingsReviewService;
use Dvsa\Olcs\Transfer\Query\Application\DeclarationUndertakings as Qry;
use Mockery as m;

/**
 * Declaration Undertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeclarationUndertakingsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeclarationUndertakings();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices['Review\ApplicationUndertakings'] = m::mock(ApplicationUndertakingsReviewService::class);
        $this->mockedSmServices['Review\VariationUndertakings'] = m::mock(VariationUndertakingsReviewService::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideReviewService')]
    public function testHandleQuery(bool $isVariation, string $reviewService): void
    {
        $query = Qry::create(['id' => 111]);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $mockApplication->expects('isGoods')->withNoArgs()->andReturn(true);
        $mockApplication->expects('isVariation')->withNoArgs()->andReturn($isVariation);

        $data = [
            'foo' => 'bar',
            'isGoods' => true,
            'isInternal' => false
        ];

        $this->mockedSmServices[$reviewService]->expects('getLongTextMarkup')->with($data)->andReturn('markup');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication);

        $mockApplication->expects('serialize')
            ->with(['licence' => ['organisation' => ['type']]])
            ->andReturn(['foo' => 'bar']);
        $mockApplication->expects('serialize')
            ->with(['licence' => ['licenceType']])
            ->andReturn(['foo' => 'bar']);

        $expected = [
            'foo' => 'bar',
            'undertakings' => 'markup'
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public static function provideReviewService(): \Iterator
    {
        yield 'new application' => [false, 'Review\ApplicationUndertakings'];
        yield 'variation' => [true, 'Review\VariationUndertakings'];
    }
}
