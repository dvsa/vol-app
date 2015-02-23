<?php

/**
 * Operator controller tests
 */
namespace OlcsTest\Controller\Operator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Operator controller tests
 */
class OperatorControllerTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Operator\OperatorController')
            ->makePartial()->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testNewApplicationActionWithGet()
    {
        $mockRequest = $this->mockRequest(false);

        $mockDateHelper = m::mock();
        $mockDateHelper->shouldReceive('getDateObject')
            ->andReturn('DATE');

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with(['receivedDate' => 'DATE']);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $this->sm->setService('Helper\Date', $mockDateHelper);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('renderView')
            ->andReturnUsing(
                function ($view, $title) {
                    return array($view, $title);
                }
            );

        $return = $this->sut->newApplicationAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $return[0]);
        $this->assertEquals('partials/form', $return[0]->getTemplate());
        $this->assertEquals($mockForm, $return[0]->getVariable('form'));
        $this->assertEquals('Create new application', $return[1]);
    }

    public function testNewApplicationActionWithPostWithInvalid()
    {
        $data = ['receivedDate' => 'DATE'];

        $mockRequest = $this->mockRequest(true);
        $mockRequest->shouldReceive('getPost')
            ->andReturn($data);

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('isValid')
            ->andReturn(false);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('renderView')
            ->andReturnUsing(
                function ($view, $title) {
                    return array($view, $title);
                }
            );

        $return = $this->sut->newApplicationAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $return[0]);
        $this->assertEquals('partials/form', $return[0]->getTemplate());
        $this->assertEquals($mockForm, $return[0]->getVariable('form'));
        $this->assertEquals('Create new application', $return[1]);
    }

    /**
     * @group operatorController
     */
    public function testNewApplicationActionWithPostWithValid()
    {
        $data = ['receivedDate' => 'DATE', 'trafficArea' => 'B'];
        $operator = 1;

        $mockRequest = $this->mockRequest(true);
        $mockRequest->shouldReceive('getPost')
            ->andReturn($data);

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($data);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockApplicationService = m::mock();
        $mockApplicationService->shouldReceive('createNew')
            ->with($operator, ['receivedDate' => $data['receivedDate']], $data['trafficArea'])
            ->andReturn(['application' => 3, 'licence' => 4]);

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('operator')
            ->andReturn($operator);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/type_of_licence', ['application' => 3])
            ->andReturn('REDIRECT');

        $return = $this->sut->newApplicationAction();

        $this->assertEquals('REDIRECT', $return);
    }

    protected function mockRequest($isPost)
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn($isPost);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        return $mockRequest;
    }
}
