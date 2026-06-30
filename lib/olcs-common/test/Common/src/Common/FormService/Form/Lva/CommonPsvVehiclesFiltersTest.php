<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\CommonPsvVehiclesFilters;

/**
 * @covers \Common\FormService\Form\Lva\CommonPsvVehiclesFilters
 */
class CommonPsvVehiclesFiltersTest extends MockeryTestCase
{
    /** @var CommonPsvVehiclesFilters  */
    protected $sut;

    /** @var  \Common\Service\Helper\FormHelperService | m\MockInterface */
    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new CommonPsvVehiclesFilters($this->formHelper);
    }

    public function testGetForm(): void
    {
        $mockForm = m::mock();

        $this->formHelper
            ->shouldReceive('createForm')->with('Lva\PsvVehicleFilter', false)->andReturn($mockForm)
            ->shouldReceive('remove')->once()->with($mockForm, 'vrm')
            ->shouldReceive('remove')->once()->with($mockForm, 'specified')
            ->shouldReceive('remove')->once()->with($mockForm, 'disc')
            ->shouldReceive('remove')->once()->with($mockForm, 'limit');

        $form = $this->sut->getForm();

        static::assertSame($mockForm, $form);
    }
}
