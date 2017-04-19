<?php

/**
 * Bus Processing Registration History Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Processing;

use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Bus Processing Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingRegistrationHistoryControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $this->sut = new \Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController();

        parent::setUp();
    }

    /**
     * @dataProvider redirectConfigProvider
     */
    public function testRedirectConfig($restResponse, $output)
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('action', null)->andReturn('delete');

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals($output, $this->sut->redirectConfig($restResponse));
    }

    /**
     * Data provider for testRedirectConfig
     *
     * @return array
     */
    public function redirectConfigProvider()
    {
        return [
            [
                [],
                [
                    'route' => 'licence/bus',
                    'params' => [
                        'action' => 'bus'
                    ]
                ]
            ],
            [
                [
                    'id' => [
                        'previousBusRegId' => 99
                    ]
                ],
                [
                    'params' => [
                        'action' => 'index',
                        'busRegId' => 99
                    ]
                ]
            ]
        ];
    }
}
