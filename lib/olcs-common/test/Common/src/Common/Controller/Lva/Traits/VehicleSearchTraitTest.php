<?php

namespace CommonTest\Common\Controller\Lva\Traits;

use Common\Service\Table\TableBuilder;
use CommonTest\Common\Controller\Lva\Traits\Stubs\VehicleSearchTraitStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Controller\Lva\Traits\VehicleSearchTrait
 */
class VehicleSearchTraitTest extends MockeryTestCase
{
    /** @var  VehicleSearchTraitStub | m\MockInterface */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new VehicleSearchTraitStub();
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider dpTestAddRemovedVehiclesActions
     */
    public function testAddRemovedVehiclesActions($filters, $actionParams): void
    {
        self::expectNotToPerformAssertions();

        /** @var TableBuilder | m\MockInterface $mockTbl */
        $mockTbl = m::mock(TableBuilder::class);

        $mockTbl
            ->shouldReceive('addAction')
            ->withArgs($actionParams);

        $this->sut->callAddRemovedVehiclesActions($filters, $mockTbl);
    }

    /**
     * @return ((bool|string)[]|string)[][][]
     *
     * @psalm-return list{array{filters: array{includeRemoved: '1'}, actionParams: list{'hide-removed-vehicles', array{label: 'label-hide-removed-vehciles', requireRows: true, keepForReadOnly: true}}}, array{filters: array{includeRemoved: '0'}, actionParams: list{'show-removed-vehicles', array{label: 'label-show-removed-vehciles', requireRows: false, keepForReadOnly: true}}}}
     */
    public function dpTestAddRemovedVehiclesActions(): array
    {
        return [
            [
                'filters' => [
                    'includeRemoved' => '1',
                ],
                'actionParams' => [
                    'hide-removed-vehicles',
                    [
                        'label' => 'label-hide-removed-vehciles',
                        'requireRows' => true,
                        'keepForReadOnly' => true,
                    ],
                ],
            ],
            [
                'filters' => [
                    'includeRemoved' => '0',
                ],
                'actionParams' => [
                    'show-removed-vehicles',
                    [
                        'label' => 'label-show-removed-vehciles',
                        'requireRows' => false,
                        'keepForReadOnly' => true,
                    ],
                ],
            ],
        ];
    }
}
