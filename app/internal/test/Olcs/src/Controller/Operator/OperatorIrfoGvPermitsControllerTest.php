<?php
/**
 * Operator Irfo Gv Permits Controller tests
 */
namespace OlcsTest\Controller\Operator;

use Olcs\Controller\Operator\OperatorIrfoGvPermitsController as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Operator Irfo Gv Permits Controller tests
 */
class OperatorIrfoGvPermitsControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function testResetAction()
    {
        $id = 1;

        $params = m::mock()
            ->shouldReceive('fromRoute')->with('id')->andReturn($id)
            ->getMock();

        $sl = m::mock()
            ->shouldReceive('get')->with('Helper\FlashMessenger')->andReturnSelf()
            ->shouldReceive('addErrorMessage')
            ->shouldReceive('addSuccessMessage')
            ->getMock();

        $redirect = m::mock()
            ->shouldReceive('toRouteAjax')->with(
                null,
                ['action' => 'index', 'id' => null],
                ['code' => '303'],
                true
            )->andReturn('redirectResponse')
            ->getMock();

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->andReturn($params)
            ->shouldReceive('getServiceLocator')
            ->andReturn($sl)
            ->shouldReceive('handleCommand')
            ->once()
            ->andReturn($response)
            ->shouldReceive('redirect')
            ->andReturn($redirect);

        $this->assertEquals('redirectResponse', $this->sut->resetAction());
    }
}
