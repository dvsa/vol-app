<?php

/**
 * External Licence Operating Centres Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

/**
 * External Licence Operating Centres Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@valtech.co.uk>
 */
class LicenceOperatingCentresControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->markTestSkipped();
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Licence\OperatingCentresController');
    }

    /**
     * @dataProvider addActionConditionalProvider
     */
    public function testAddAction($conditional)
    {
        // Mocks
        $mockProcessingService = m::mock();
        $this->setService('Processing\CreateVariation', $mockProcessingService);
        $mockForm = m::mock('\Zend\Form\Form');

        // Expectations
        $this->sut->shouldReceive('render')
            ->with(
                'oc-create-variation-confirmation-title',
                $mockForm,
                ['sectionText' => 'oc-create-variation-confirmation-message']
            )
            ->andReturn('RENDER');

        $mockProcessingService->shouldReceive('getForm')
            ->with($this->request)
            ->andReturn($mockForm);

        // @NOTE RC: The data provider provides multiple routes into the same if statement
        // I think this solution is quite elegant, rather than duplicating the test with all of the same expectations
        // I don't think this solution should be used on complicated units of code with multiple nested conditionals etc
        // but for units of code where there is just a single conditional with multiple routes in, I think this fits the
        // bill nicely
        $conditional($this->request, $mockForm);

        $this->assertEquals('RENDER', $this->sut->addAction());
    }

    public function testAddActionWithPost()
    {
        $formData = [
            'foo' => 'bar'
        ];

        // Mocks
        $mockProcessingService = m::mock();
        $this->setService('Processing\CreateVariation', $mockProcessingService);
        $mockForm = m::mock('\Zend\Form\Form');

        // Expectations
        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn(123);

        $mockForm->shouldReceive('isValid')
            ->andReturn(true);

        $mockProcessingService->shouldReceive('getForm')
            ->with($this->request)
            ->andReturn($mockForm)
            ->shouldReceive('getDataFromForm')
            ->with($mockForm)
            ->andReturn($formData)
            ->shouldReceive('createVariation')
            ->with(123, $formData)
            ->andReturn(321);

        $this->request->shouldReceive('isPost')
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => 321])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->addAction());
    }

    public function testDeleteWithMoreRowsThanParams()
    {
        $this->sut->shouldReceive('getAdapter')
            ->andReturn(
                m::mock()
                ->shouldReceive('getTableData')
                ->andReturn([1, 2])
                ->getMock()
            )
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn('')
            ->shouldReceive('render')
            ->andReturn('RENDER');

        $mockForm = m::mock('\Zend\Form\Form');

        $this->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('createFormWithRequest')
            ->with('GenericDeleteConfirmation', $this->request)
            ->andReturn($mockForm)
            ->getMock()
        );

        $this->assertEquals('RENDER', $this->sut->deleteAction());
    }

    public function testDeleteWithMorParamsThanRows()
    {
        $this->sut->shouldReceive('getAdapter')
            ->andReturn(
                m::mock()
                ->shouldReceive('getTableData')
                ->andReturn([])
                ->getMock()
            )
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('licence')
            ->andReturn(123)
            ->shouldReceive('redirect->toRouteAjax')
            ->with('create_variation', [], [], true);

        $mockForm = m::mock('\Zend\Form\Form');

        $mockForm->shouldReceive('get->get->setLabel')
            ->with('create-variation-button');

        $this->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('createFormWithRequest')
            ->with('GenericConfirmation', $this->request)
            ->andReturn($mockForm)
            ->getMock()
        );

        $this->setService(
            'Helper\Url',
            m::mock()
            ->shouldReceive('fromRoute')
            ->with('lva-licence/variation', ['licence' => 123])
            ->andReturn('foo')
            ->getMock()
        );

        $this->setService(
            'Helper\Translation',
            m::mock()
            ->shouldReceive('translateReplace')
            ->with('variation-required-message-prefix', ['foo'])
            ->getMock()
        );

        $this->sut->deleteAction();
    }

    public function testDeleteWithMorParamsThanRowsAndPost()
    {
        $this->sut->shouldReceive('getAdapter')
            ->andReturn(
                m::mock()
                ->shouldReceive('getTableData')
                ->andReturn([])
                ->getMock()
            )
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn('1')
            ->shouldReceive('redirect->toRouteAjax')
            ->with('create_variation', [], [], true)
            ->andReturn('REDIRECT');

        $this->setPost([]);

        $this->assertEquals('REDIRECT', $this->sut->deleteAction());
    }

    public function addActionConditionalProvider()
    {
        return [
            'Without post' => [
                function ($mockRequest) {
                    $mockRequest->shouldReceive('isPost')
                        ->andReturn(false);
                }
            ],
            'Without valid form' => [
                function ($mockRequest, $mockForm) {
                    $mockRequest->shouldReceive('isPost')
                        ->andReturn(true);

                    $mockForm->shouldReceive('isValid')
                        ->andReturn(false);
                }
            ]
        ];
    }
}
