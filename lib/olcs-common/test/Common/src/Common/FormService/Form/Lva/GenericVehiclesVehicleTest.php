<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\GenericVehiclesVehicle;

/**
 * Generic Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenericVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new GenericVehiclesVehicle($this->formHelper);
    }

    public function testAlterFormNoOp(): void
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'add',
            'isPost' => false,
            'canAddAnother' => true
        ];

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }

    public function testAlterFormAddCantAddAnother(): void
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'add',
            'isPost' => false,
            'canAddAnother' => false
        ];

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('addAnother')
                ->getMock()
            );

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }

    public function testAlterFormEdit(): void
    {
        $mockForm = m::mock();
        $params = [
            'mode' => 'edit',
            'isPost' => false,
            'canAddAnother' => false
        ];

        $this->formHelper->shouldReceive('disableElement')
            ->with($mockForm, 'data->vrm');

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('addAnother')
                ->getMock()
            );

        $this->assertNull($this->sut->alterForm($mockForm, $params));
    }
}
