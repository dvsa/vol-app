<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicencePsvVehicles;

/**
 * Licence Psv Vehicles Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePsvVehiclesTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService')->makePartial();
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new LicencePsvVehicles();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testGetForm()
    {
        $mockLicenceVehicles = m::mock('\Common\FormService\FormServiceInterface');
        $this->fsm->setService('lva-licence-vehicles_psv', $mockLicenceVehicles);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('saveAndContinue');
        $formActions->shouldReceive('remove')->with('saveAndContinue');
        $formActions->shouldReceive('has')->with('cancel');
        $formActions->shouldReceive('remove')->with('cancel');
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('class', 'action--primary large')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockForm = m::mock();
        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\PsvVehicles')
            ->andReturn($mockForm)
            ->getMock();

        $this->sut->getForm();

    }
}
