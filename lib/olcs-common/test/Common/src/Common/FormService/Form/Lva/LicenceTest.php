<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\Licence;

class LicenceTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $authService;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->authService = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);
        $this->sut = new Licence($this->formHelper, $this->authService);
    }

    public function testAlterForm(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $form->shouldReceive('has')
            ->with('form-actions')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('has')
                    ->with('saveAndContinue')
                    ->andReturn(true)
                    ->shouldReceive('has')
                    ->with('save')
                    ->andReturn(true)
                    ->shouldReceive('get')
                    ->with('save')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setAttribute')
                            ->once()
                            ->with('class', 'govuk-button')
                            ->getMock()
                    )
                    ->shouldReceive('remove')
                    ->once()
                    ->with('saveAndContinue')
                    ->getMock()
            );

        $this->assertNull($this->sut->alterForm($form));
    }
}
