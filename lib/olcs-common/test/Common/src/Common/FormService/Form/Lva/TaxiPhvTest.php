<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Taxi Phv Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    /**
     * @var TaxiPhv
     */
    private $sut;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->authService = m::mock(AuthorizationService::class);

        $this->sut = new TaxiPhv($this->formHelper, $this->authService);
    }

    public function testGetForm(): void
    {
        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->with('save');
        $formActions->shouldReceive('remove')->with('cancel');

        $form = m::mock(\Common\Form\Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()->with('Lva\TaxiPhv')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
