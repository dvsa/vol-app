<?php

namespace CommonTest\Common\FormService\Form\Lva\TransportManager;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\TransportManager\ApplicationTransportManager as Sut;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application TransportManager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTransportManagerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    protected $sut;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->authService = m::mock(AuthorizationService::class);

        $this->sut = new Sut($this->formHelper, $this->authService);
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
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->sut->getForm();
    }

    public function testGetFormWithoutFormActions(): void
    {
        $form = m::mock(\Common\Form\Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(false);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->sut->getForm();
    }

    public function testGetFormWithoutFormAction(): void
    {
        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');

        $formActions->shouldReceive('has')->with('cancel')->andReturn(false);
        $formActions->shouldReceive('remove')->never()->with('cancel');

        $form = m::mock(\Common\Form\Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
