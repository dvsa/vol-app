<?php

/**
 * GracePeriodTest.php
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

use Common\BusinessService\Response;

use OlcsTest\Bootstrap;

use Olcs\BusinessService\Service\Lva\GracePeriod;

/**
 * Class GracePeriodTest
 *
 * GracePeriod test.
 *
 * @package OlcsTest\BusinessService\Service\Lva
 */
class GracePeriodTest extends MockeryTestCase
{
    protected $sut = null;

    protected $sm = null;

    public function setUp()
    {
        $this->sut = new GracePeriod();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcessSave()
    {
        $params = array();
        $expected = \Common\BusinessService\ResponseInterface::TYPE_SUCCESS;

        $this->sm->shouldReceive('get')->with('Entity\GracePeriod')->andReturn(
            m::mock()
                ->shouldReceive('save')
                ->with($params)
                ->getMock()
        );

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals($response->getType(), $expected);
    }

    public function testProcessUpdate()
    {
        $params = array(
            'id' => 1
        );
        $expected = \Common\BusinessService\ResponseInterface::TYPE_SUCCESS;

        $this->sm->shouldReceive('get')->with('Entity\GracePeriod')->andReturn(
            m::mock()
                ->shouldReceive('update')
                ->with($params['id'], $params)
                ->getMock()
        );

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals($response->getType(), $expected);
    }
}
