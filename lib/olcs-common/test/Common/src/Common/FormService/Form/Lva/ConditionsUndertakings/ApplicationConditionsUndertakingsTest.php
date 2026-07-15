<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva\ConditionsUndertakings;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\ConditionsUndertakings\ApplicationConditionsUndertakings as Sut;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Conditions Undertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ApplicationConditionsUndertakingsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $authService = m::mock(AuthorizationService::class);

        $this->sut = new Sut($this->formHelper, $authService);
    }

    public function testGetForm(): void
    {
        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $form = m::mock(\Common\Form\Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\ConditionsUndertakings')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
