<?php

/**
 * Class Search Controller Test
 */

namespace OlcsTest\Controller\Search;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\Search\SearchController as Sut;

/**
 * Class Search Controller Test
 */
class SearchControllerTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexActionWithoutIndex(): void
    {
        $this->sut->shouldReceive('params->fromRoute')
            ->with('index')
            ->once()
            ->andReturn(null);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('search/index', $view->getTemplate());
    }
}
