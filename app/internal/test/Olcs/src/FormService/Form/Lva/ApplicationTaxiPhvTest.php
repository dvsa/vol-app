<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationTaxiPhv;

class ApplicationTaxiPhvTest extends MockeryTestCase
{
    /** @var  ApplicationTaxiPhv */
    protected $sut;

    /** @var  m\MockInterface|\Common\Service\Helper\FormHelperService */
    protected $formHelper;
    /** @var  \Common\FormService\FormServiceManager */
    protected $fsm;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new ApplicationTaxiPhv($this->formHelper, m::mock(\LmcRbacMvc\Service\AuthorizationService::class));
    }

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true)->once();
        $formActions->shouldReceive('remove')->once()->with('cancel')->once();
        $formActions->shouldReceive('has')->with('save')->andReturn(true)->once();
        $formActions->shouldReceive('remove')->with('save')->once();

        $form = m::mock();
        $form->shouldReceive('has')->with('form-actions')->andReturn(true)->twice();
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions)->twice();

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TaxiPhv')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
