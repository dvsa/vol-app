<?php

/**
 * Test MyDetailsController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerAddEditHelper;
use Admin\Controller\MyDetailsController;

/**
 * Test MyDetailsController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class MyDetailsControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        //used for testing actions other than index action
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new MyDetailsController();
    }

    public function testEditAction()
    {
        $loggedInUser = 1;
        $formData = [];
        $this->sut->setLoggedInUser($loggedInUser);

        $addEditHelper = new ControllerAddEditHelper();

        //mock user service
        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('fetchMyDetailsFormData')->with($loggedInUser)->andReturn($formData);

        $mockServiceManager = $addEditHelper->getServiceManager('edit', [], 'myDetails');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\User')
            ->andReturn($mockUserService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->editAction());

    }

    /**
     * Tests the redirect action
     */
    public function testRedirectAction()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['redirect' => 'Redirect']
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'admin-dashboard/admin-my-details/details',
            ['action'=>'edit'],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectAction());
    }

    /**
     * Tests the processSave method
     */
    public function testProcessSave()
    {
        $id = 1;
        $data = ['id' => $id];

        //mock user service
        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('save')->with($data)->andReturn($id);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\User')
            ->andReturn($mockUserService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($id, $this->sut->processSave($data));
    }

    /**
     * Tests the processSave method
     *
     * @dataProvider processSaveExceptionProvider
     *
     * @param $expectedException
     * @param $message
     */
    public function testProcessSaveException($expectedException, $message)
    {
        $id = 1;
        $data = ['id' => $id];

        $class = 'Common\Exception\\' . $expectedException;

        //mock user service
        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('save')->with($data)->andThrow(new $class($message));

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\User')
            ->andReturn($mockUserService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals(false, $this->sut->processSave($data));
    }

    /**
     * Data provider for testProcessSaveException
     *
     * @return array
     */
    public function processSaveExceptionProvider()
    {
        return [
            [
                'BadRequestException',
                'Error message 1'
            ],
            [
                'ResourceNotFoundException',
                'Error message 2'
            ],
        ];
    }

}
