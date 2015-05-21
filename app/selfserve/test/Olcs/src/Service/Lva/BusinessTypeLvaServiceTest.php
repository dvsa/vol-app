<?php

/**
 * Business Type LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Service\Lva;

use Olcs\Service\Lva\BusinessTypeLvaService;
use Mockery as m;

/**
 * Business Type LVA service test
 *
 * @todo Migrate these tests to the form service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessTypeLvaServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function setup()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');
        $this->form = m::mock('\Zend\Form\Form');
        $this->sut = new BusinessTypeLvaService();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testLockType()
    {
        $element = m::mock();

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('type')
                ->andReturn($element)
                ->getMock()
            );

        $this->sm->shouldReceive('get')
            ->with('Helper\Form')
            ->andReturn(
                m::mock()
                ->shouldReceive('lockElement')
                ->with($element, 'business-type.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->type')
                ->getMock()
            );

        $this->sut->lockType($this->form);
    }
}
