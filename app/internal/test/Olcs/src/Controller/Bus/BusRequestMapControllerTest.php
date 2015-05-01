<?php


namespace OlcsTest\Controller\Bus;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Controller\Bus\BusRequestMapController;
use Olcs\Service\Data\RequestMap;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Zend\Form\FormInterface;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Placeholder;
use Zend\View\Model\ViewModel;

/**
 * Class BusRequestMapControllerTest
 * @package OlcsTest\Controller\Bus
 */
class BusRequestMapControllerTest extends TestCase
{
    public function testGet()
    {
        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldIgnoreMissing($mockForm);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->shouldIgnoreMissing($mockPlaceholder);

        $mockCtrl = m::mock(BusRequestMapController::class);
        $mockCtrl->shouldAllowMockingProtectedMethods();
        $mockCtrl->shouldReceive('renderView')->andReturn('rendered');

        $mockServiceLocator = m::mock(ServiceLocatorInterface::class);
        $mockServiceLocator->shouldReceive('get')->with('Helper\Form')->andReturn($mockForm);
        $mockServiceLocator->shouldReceive('get')->with('viewHelperManager')->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockServiceLocator->shouldReceive('get')->with('ControllerManager')->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('BusRequestMapController')->andReturn($mockCtrl);

        $mockRequest = new Request();
        $mockRequest->setMethod('GET');

        $setRequest = function ($request) {
            $this->request = $request;
        };

        $sut = new BusRequestMapController();
        $setRequest = $setRequest->bindTo($sut, $sut);
        $setRequest($mockRequest);

        $sut->setServiceLocator($mockServiceLocator);

        $response = $sut->requestMapAction();

        $this->assertEquals('rendered', $response);
    }

    public function testPost()
    {
        $plugins = [
            'params' => 'Params',
            'flashMessenger' => 'FlashMessenger',
            'redirect' => 'Redirect'
        ];

        $data = ['fields' => ['scale' => 'small']];

        $mockPluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $mockPluginManagerHelper->getMockPluginManager($plugins);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromPost')->andReturn($data);
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn(75);

        $mockFm = $mockPluginManager->get('flashMessenger', '');
        $mockFm->shouldReceive('addSuccessMessage')->with('Map created successfully');

        $mockRd = $mockPluginManager->get('redirect', '');
        $mockRd->shouldReceive('toRouteAjax')->andReturn('redirect');

        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldReceive('createFormWithRequest')->andReturnSelf();
        $mockForm->shouldReceive('isValid')->andReturn(true);
        $mockForm->shouldReceive('setData')->andReturnSelf();
        $mockForm->shouldReceive('getData')->andReturn($data);

        $mockDs = m::mock(RequestMap::class);
        $mockDs->shouldReceive('requestMap')->with(75, 'small');

        $mockServiceLocator = m::mock(ServiceLocatorInterface::class);
        $mockServiceLocator->shouldReceive('get')->with('Helper\Form')->andReturn($mockForm);
        $mockServiceLocator->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with(RequestMap::class)->andReturn($mockDs);

        $mockRequest = new Request();
        $mockRequest->setMethod('POST');

        $setRequest = function ($request) {
            $this->request = $request;
        };

        $sut = new BusRequestMapController();
        $setRequest = $setRequest->bindTo($sut, $sut);
        $setRequest($mockRequest);

        $sut->setServiceLocator($mockServiceLocator);
        $sut->setPluginManager($mockPluginManager);

        $response = $sut->requestMapAction();

        $this->assertEquals('redirect', $response);
    }
}
