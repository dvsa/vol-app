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

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexActionWithoutIndex()
    {
        $this->sut->shouldReceive('params->fromRoute')
            ->with('index')
            ->once()
            ->andReturn(null);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('search/index', $view->getTemplate());
    }
}
