<?php

/**
 * Create Variation Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Service\Processing\CreateVariationProcessingService;

/**
 * Create Variation Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationProcessingServiceTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new CreateVariationProcessingService();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetDataFromForm()
    {
        $form = m::mock('\Zend\Form\Form');

        $this->assertEquals([], $this->sut->getDataFromForm($form));
    }

    public function testCreateVariation()
    {
        $licenceId = 123;
        $data = ['foo' => 'bar'];

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId, $data)
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariation($licenceId, $data));
    }

    public function testGetForm()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');

        // Mocks
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenericConfirmation')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('submit')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('create-variation-button')
                    ->getMock()
                )->getMock()
            );

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }

    public function testGetFormWithPost()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenericConfirmation')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('submit')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('create-variation-button')
                    ->getMock()
                )->getMock()
            );

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }
}