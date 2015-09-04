<?php
/**
 * Bus Registration decision controller tests
 */
namespace OlcsTest\Controller\Bus\Processing;

use Olcs\Controller\Bus\Processing\BusProcessingDecisionController as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\RefData;

/**
 * Bus Registration decision controller tests
 */
class BusProcessingDecisionControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    /**
     * @dataProvider grantActionDataProvider
     */
    public function testGrantAction(
        $busRegId,
        $busRegData,
        $expectedHandleCommand,
        $expectedProcessGrantVariation
    ) {
        $params = m::mock()
            ->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId)
            ->getMock();

        $sl = m::mock()
            ->shouldReceive('get')->with('Helper\FlashMessenger')->andReturnSelf()
            ->shouldReceive('addErrorMessage')
            ->shouldReceive('addSuccessMessage')
            ->getMock();

        $redirect = m::mock()
            ->shouldReceive('toRouteAjax')->with(
                'licence/bus-processing/decisions',
                ['action' => 'details'],
                ['code' => '303'],
                true
            )->andReturn('redirectResponse')
            ->getMock();

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(!empty($busRegData))
            ->shouldReceive('getResult')
            ->andReturn($busRegData)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->andReturn($params)
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response)
            ->shouldReceive('getServiceLocator')
            ->andReturn($sl)
            ->shouldReceive('handleCommand')
            ->times($expectedHandleCommand ? 1 :0)
            ->andReturn($response)
            ->shouldReceive('processGrantVariation')
            ->times($expectedProcessGrantVariation ? 1 :0)
            ->andReturn('redirectResponse')
            ->shouldReceive('redirect')
            ->andReturn($redirect);

        $this->assertEquals('redirectResponse', $this->sut->grantAction());
    }

    public function grantActionDataProvider()
    {
        return [
            // record not found
            [
                100,
                [],
                false,
                false,
            ],
            // non-grantable
            [
                100,
                [
                    'id' => 100,
                    'isGrantable' => false,
                ],
                false,
                false,
            ],
            // grantable - new
            [
                100,
                [
                    'id' => 100,
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_NEW,
                    ],
                ],
                true,
                false,
            ],
            // grantable - variation
            [
                100,
                [
                    'id' => 100,
                    'isGrantable' => true,
                    'status' => [
                        'id' => RefData::BUSREG_STATUS_VARIATION,
                    ],
                ],
                false,
                true,
            ],
        ];
    }
}
