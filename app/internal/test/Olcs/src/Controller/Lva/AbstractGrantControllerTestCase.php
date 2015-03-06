<?php

/**
 * Abstract Grant Controller Test Case
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Abstract Grant Controller Test Case
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractGrantControllerTestCase extends AbstractLvaControllerTestCase
{

    protected $translatorMock;

    protected $guidanceMock;

    protected $controllerClass;

    public function setUp()
    {
        parent::setUp();

        $this->mockController($this->controllerClass);

        $this->translatorMock = m::mock();
        $this->setService('Helper\Translation', $this->translatorMock);

        $this->guidanceMock = m::mock();
        $this->mockService('ViewHelperManager', 'get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getContainer')
                    ->andReturn($this->guidanceMock)
                    ->getMock()
            );
    }

    protected function expectGuidanceMessage($message)
    {
        $this->guidanceMock
            ->shouldReceive('append')
            ->once()
            ->with($message);
    }

    protected function mockConfirmForm()
    {
        $mockForm = $this->createMockForm('GenericConfirmation');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('confirm-grant-application');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);

        return $mockForm;
    }
}
