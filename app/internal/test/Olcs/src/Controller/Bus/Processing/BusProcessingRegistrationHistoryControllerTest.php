<?php

declare(strict_types=1);

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
final class BusProcessingRegistrationHistoryControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $pluginManagerHelper;

    public function setUp(): void
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $flashMessengerHelper =  m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $this->sut = new \Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController($translationHelper, $formHelper, $flashMessengerHelper, $navigation);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('redirectConfigProvider')]
    public function testRedirectConfig(mixed $restResponse, mixed $output): void
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function redirectConfigProvider(): \Iterator
    {
        yield [
            [],
            [
                'route' => 'licence/bus',
                'params' => [
                    'action' => 'bus'
                ]
            ]
        ];
        yield [
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
        ];
    }
}
