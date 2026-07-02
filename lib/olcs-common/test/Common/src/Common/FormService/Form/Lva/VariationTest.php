<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\Variation;
use LmcRbacMvc\Service\AuthorizationService;

class VariationTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $formHelper;
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->authService = m::mock(AuthorizationService::class);
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->sut = new Variation($this->formHelper, $this->authService);
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
