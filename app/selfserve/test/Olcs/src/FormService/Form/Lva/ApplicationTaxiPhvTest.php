<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\ApplicationTaxiPhv;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Taxi Phv Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTaxiPhvTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationTaxiPhv
     */
    private $sut;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->sut = new ApplicationTaxiPhv($this->formHelper, m::mock(AuthorizationService::class));
    }

    public function testGetForm(): void
    {
        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->with('save');
        $formActions->shouldReceive('remove')->with('saveAndContinue');
        $formActions->shouldReceive('remove')->with('cancel');

        $formActions->shouldReceive('add')->with(m::type(BackToApplicationActionLink::class));

        $form = m::mock(Form::class);
        $this->mockAlterButtons($form, $this->formHelper, $formActions);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()->with('Lva\TaxiPhv')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
