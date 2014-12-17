<?php

/**
 * Variation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Variation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationControllerTest extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\VariationController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group licence-variation-controller
     */
    public function testIndexAction()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest);

        $form = m::mock('\Zend\Form\Form');

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('GenericConfirmation')
            ->andReturn($form)
            ->shouldReceive('setFormActionFromRequest')
            ->with($form, $mockRequest);

        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('render')
            ->with('create-variation-confirmation', $form, ['sectionText' => 'licence.variation.confirmation.text'])
            ->andReturn('RENDER');

        $this->assertEquals('RENDER', $this->sut->indexAction());
    }

    /**
     * @group licence-variation-controller
     */
    public function testIndexActionWithPost()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getIdentifier')
            ->andReturn(3);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => 5])
            ->andReturn('REDIRECT');

        $mockAppService = m::mock();
        $mockAppService->shouldReceive('createVariation')
            ->with(3)
            ->andReturn(5);

        $this->sm->setService('Entity\Application', $mockAppService);

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }
}
