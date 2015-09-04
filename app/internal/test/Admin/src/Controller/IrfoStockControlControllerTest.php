<?php
/**
 * IrfoStockControlControllerTest
 */
namespace AdminTest\Controller;

use Admin\Controller\IrfoStockControlController as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\RefData;

/**
 * IrfoStockControlControllerTest
 */
class IrfoStockControlControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function testInStockAction()
    {
        $this->sut
            ->shouldReceive('update')
            ->once()
            ->with(RefData::IRFO_STOCK_CONTROL_STATUS_IN_STOCK)
            ->andReturn('redirectResponse');

        $this->assertEquals('redirectResponse', $this->sut->inStockAction());
    }

    public function testIssuedAction()
    {
        $this->sut
            ->shouldReceive('update')
            ->once()
            ->with(RefData::IRFO_STOCK_CONTROL_STATUS_ISSUED)
            ->andReturn('redirectResponse');

        $this->assertEquals('redirectResponse', $this->sut->issuedAction());
    }

    public function testVoidAction()
    {
        $this->sut
            ->shouldReceive('update')
            ->once()
            ->with(RefData::IRFO_STOCK_CONTROL_STATUS_VOID)
            ->andReturn('redirectResponse');

        $this->assertEquals('redirectResponse', $this->sut->voidAction());
    }

    public function testReturnedAction()
    {
        $this->sut
            ->shouldReceive('update')
            ->once()
            ->with(RefData::IRFO_STOCK_CONTROL_STATUS_RETURNED)
            ->andReturn('redirectResponse');

        $this->assertEquals('redirectResponse', $this->sut->returnedAction());
    }

    /**
     * @dataProvider updateDataProvider
     */
    public function testUpdate($isOk)
    {
        $id = '1,2';

        $params = m::mock()
            ->shouldReceive('fromRoute')->with('id')->andReturn($id)
            ->getMock();

        $sl = m::mock()
            ->shouldReceive('get')->with('Helper\FlashMessenger')->andReturnSelf()
            ->shouldReceive('addErrorMessage')->times($isOk ? 0 : 1)
            ->shouldReceive('addSuccessMessage')->times($isOk ? 1 : 0)
            ->getMock();

        $redirect = m::mock()
            ->shouldReceive('toRouteAjax')
            ->with(
                null,
                ['action' => 'index'],
                ['code' => '303'],
                false
            )
            ->once()
            ->andReturn('redirectResponse')
            ->getMock();

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn($isOk)
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
            ->once()
            ->andReturn($redirect);

        $this->assertEquals('redirectResponse', $this->sut->update(RefData::IRFO_STOCK_CONTROL_STATUS_VOID));
    }

    public function updateDataProvider()
    {
        return [
            // isOK - true
            [
                true,
            ],
            // isOK - false
            [
                false,
            ],
        ];
    }
}
