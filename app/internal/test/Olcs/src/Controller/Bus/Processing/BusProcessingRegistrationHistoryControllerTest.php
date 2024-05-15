<?php

/**
 * Bus Processing Registration History Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller\Bus\Processing;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Dvsa\OlcsTest\Controller\ControllerPluginManagerHelper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Bus Processing Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingRegistrationHistoryControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $translationHelper;
    protected $formHelper;
    protected $flashMessengerHelper;
    protected $navigation;
    protected $pluginManagerHelper;

    public function setUp(): void
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->flashMessengerHelper =  m::mock(FlashMessengerHelperService::class);
        $this->navigation = m::mock(Navigation::class);

        $this->sut = new \Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController($this->translationHelper, $this->formHelper, $this->flashMessengerHelper, $this->navigation);

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

        $mockParams = $mockPluginManager->get('params');
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
