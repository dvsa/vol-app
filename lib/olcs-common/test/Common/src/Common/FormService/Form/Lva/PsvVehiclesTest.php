<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Common\FormService\Form\Lva\PsvVehicles;
use LmcRbacMvc\Service\AuthorizationService;

class PsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    protected $classToTest = PsvVehicles::class;

    protected $formName = 'Lva\PsvVehicles';

    #[\Override]
    protected function setUp(): void
    {
        $this->authService = m::mock(AuthorizationService::class);
        $this->classArgs = [$this->authService];
        parent::setUp();
    }

    #[\Override]
    public function testGetForm(): void
    {
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->with($this->formName)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'shareInfo');

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
