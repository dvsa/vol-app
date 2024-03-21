<?php

/**
 * SearchFilterFieldset Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */

namespace OlcsTest\Form\Element;

use Olcs\Form\Element\SearchFilterFieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SearchFilterFieldset Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */
class SearchFilterFieldsetTest extends TestCase
{
    public function testSearchAwareTraitByProxy()
    {
        $service = m::mock(\Common\Service\Data\Search\Search::class);

        $sut = new SearchFilterFieldset();

        $this->assertSame($service, $sut->setSearchService($service)->getSearchService());
    }

    public function initTest()
    {
        $filter = m::mock('stdClass');
        $filter->shouldReceive('getKey')->twice()->andReturn('Key');
        $filter->shouldReceive('getTitle')->twice()->andReturn('Title');
        $filters = [$filter, $filter];

        $service = m::mock(\Common\Service\Data\Search\Search::class);
        $service->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);

        $sut = new SearchFilterFieldset();

        $this->assertCount($sut->count(), 2);
    }
}
