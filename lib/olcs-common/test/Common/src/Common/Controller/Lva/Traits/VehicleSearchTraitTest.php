<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva\Traits;

use Common\Service\Table\TableBuilder;
use CommonTest\Common\Controller\Lva\Traits\Stubs\VehicleSearchTraitStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversTrait(\Common\Controller\Lva\Traits\VehicleSearchTrait::class)]
final class VehicleSearchTraitTest extends MockeryTestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestAddRemovedVehiclesActions')]
    public function testAddRemovedVehiclesActions($filters, $actionParams): void
    {
        /** @var TableBuilder | m\MockInterface $mockTbl */
        $mockTbl = m::mock(TableBuilder::class);

        $mockTbl
            ->expects('addAction')
            ->withArgs($actionParams);

        $this->sut->callAddRemovedVehiclesActions($filters, $mockTbl);
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(bool | string)> | string)>>>
     *
     * @psalm-return list{array{filters: array{includeRemoved: '1'}, actionParams: list{'hide-removed-vehicles', array{label: 'label-hide-removed-vehciles', requireRows: true, keepForReadOnly: true}}}, array{filters: array{includeRemoved: '0'}, actionParams: list{'show-removed-vehicles', array{label: 'label-show-removed-vehciles', requireRows: false, keepForReadOnly: true}}}}
     */
    public static function dpTestAddRemovedVehiclesActions(): \Iterator
    {
        yield [
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
        ];
        yield [
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
        ];
    }
}
