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
use Common\Service\Data\FeeTypeDataService;

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

        $form->shouldReceive('getData')
            ->andReturn(['data' => ['foo' => 'bar']]);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getDataFromForm($form));
    }

    public function testCreateVariationWithoutFee()
    {
        $licenceId = 123;
        $data = [
            'foo' => 'bar',
            'feeRequired' => 'N'
        ];

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId, ['foo' => 'bar'])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariation($licenceId, $data));
    }

    public function testCreateVariationWithFee()
    {
        // Params
        $licenceId = 123;
        $data = [
            'foo' => 'bar',
            'feeRequired' => 'Y'
        ];

        // Mocks
        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);
        $mockProcessingService = m::mock();
        $this->sm->setService('Processing\Application', $mockProcessingService);

        // Expectations
        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId, ['foo' => 'bar'])
            ->andReturn(321);

        $mockProcessingService->shouldReceive('createFee')
            ->with(321, 123, FeeTypeDataService::FEE_TYPE_VAR);

        $this->assertEquals(321, $this->sut->createVariation($licenceId, $data));
    }

    public function testGetForm()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');

        // Mocks
        $mockForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('CreateVariation')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockDateHelper->shouldReceive('getDate')
            ->andReturn('2014-01-02');

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['receivedDate' => '2014-01-02']]);

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }

    public function testGetFormWithPost()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('CreateVariation')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData);

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }
}
