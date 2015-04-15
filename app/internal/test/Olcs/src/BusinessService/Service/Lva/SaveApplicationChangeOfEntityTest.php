<?php

/**
 * SaveApplicationChangeOfEntityTest.php
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Lva\SaveApplicationChangeOfEntity as Sut;
use Common\BusinessService\Response;
use OlcsTest\Bootstrap;

/**
 * Save Application Change Of Entity Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class SaveApplicationChangeOfEntityTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $brm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new Sut();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($params)
    {
        $mockApplicationEntityService = m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($params['application']);

        $this->sm->setService('Entity\Application', $mockApplicationEntityService->getMock());

        $mockChangeEntityService = m::mock()
            ->shouldReceive('save');

        $this->sm->setService('Entity\ChangeOfEntity', $mockChangeEntityService->getMock());

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
    }

    public function processDataProvider()
    {
        return array(
            array(
                array(
                    'details' => array(),
                    'application' => 1,
                    'changeOfEntity' => null,
                ),
            ),
            array(
                array(
                    'details' => array(),
                    'application' => 1,
                    'changeOfEntity' => array(
                        'id' => 1,
                        'version' => 1
                    ),
                ),
            ),
        );
    }
}
