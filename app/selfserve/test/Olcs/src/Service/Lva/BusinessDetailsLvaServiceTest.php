<?php

/**
 * Business Details LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Service\Lva;

use Olcs\Service\Lva\BusinessDetailsLvaService;
use Mockery as m;

/**
 * Business Details LVA service test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsLvaServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function setup()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');
        $this->form = m::mock('\Zend\Form\Form');
        $this->sut = new BusinessDetailsLvaService();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testLockDetails()
    {
        $number = m::mock();
        $name = m::mock();

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('companyNumber')
                ->andReturn($number)
                ->shouldReceive('get')
                ->with('name')
                ->andReturn($name)
                ->getMock()
            );

        $this->sm->shouldReceive('get')
            ->with('Helper\Form')
            ->andReturn(
                m::mock()
                ->shouldReceive('lockElement')
                ->with($number, 'business-details.company_number.locked')
                ->shouldReceive('lockElement')
                ->with($name, 'business-details.name.locked')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->companyNumber->company_number')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->companyNumber->submit_lookup_company')
                ->shouldReceive('disableElement')
                ->with($this->form, 'data->name')
                ->getMock()
            );

        $this->sut->lockDetails($this->form);
    }
}
