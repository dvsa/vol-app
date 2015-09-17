<?php
/**
 * Class Index Controller Test
 */
namespace OlcsTest\Controller;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\IndexController as Sut;

/**
 * Class Index Controller Test
 */
class IndexControllerTest extends TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexAction()
    {
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(false);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('search', [], ['code' => 303], false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionWithDashboard()
    {
        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('dashboard', [], ['code' => 303], false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }
}
