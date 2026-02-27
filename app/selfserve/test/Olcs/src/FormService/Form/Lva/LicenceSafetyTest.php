<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceSafety;

/**
 * Licence Safety Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceSafetyTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new LicenceSafety($this->formHelper);
    }

    public function testGetForm(): void
    {
        $mockLicenceVehicles = m::mock(Form::class);
        $this->fsm->setService('lva-licence-vehicles_psv', $mockLicenceVehicles);

        $formActions = m::mock();
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('class', 'govuk-button')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockForm = m::mock();
        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\Safety')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $this->sut->getForm();
    }
}
