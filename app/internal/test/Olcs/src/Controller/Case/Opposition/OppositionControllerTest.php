<?php

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Helper\Placeholder;

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Opposition\OppositionController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        parent::setUp();
    }

    /**
     * @dataProvider indexActionDataProvider
     *
     * @param $receivedDate
     * @param $adPlacedDate
     * @param $oorDate
     */
    public function testIndexAction($receivedDate, $adPlacedDate, $oorDate)
    {
        $id = 1;

        $listData = [
            'Results' => [
                0 => [
                    'application' => [
                        'receivedDate' => $receivedDate,
                        'operatingCentres' => [
                            0 => [
                                'adPlacedDate' => $adPlacedDate
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedViewVars = [
            'oooDate' => null,
            'oorDate' => $oorDate
        ];

        $caseId = 24;
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params', 'url' => 'Url']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockUrl = $mockPluginManager->get('url', '');
        $mockParams->shouldReceive('fromPost')->with('action')->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('page', 1)->andReturn(1);
        $mockParams->shouldReceive('fromQuery')->with('sort', 'id')->andReturn('id');
        $mockParams->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn('DESC');
        $mockParams->shouldReceive('fromQuery')->with('limit', '10')->andReturn(10);
        $mockParams->shouldReceive('fromQuery')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Complaint',
            'GET',
            m::type('array'),
            m::type('array')
        )->andReturn([]);

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Opposition',
            'GET',
            m::type('array'),
            m::type('array')
        )->andReturn([]);

        //placeholders
        $placeholder = new Placeholder();

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);

        $this->sut->setPluginManager($mockPluginManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->indexAction();
    }

    public function indexActionDataProvider()
    {
        return [
            ['2014-04-01T09:43:21+0100', '2014-04-01', '2014-04-22T00:00:00+0100'], //dates are fine
            //['2014-04-02T09:43:21+0100', '2014-04-01', null], //received is before the ad placed date
            //['2014-04-02T09:43:21+0100', null, null] //we don't have an ad placed date
        ];
    }
}
