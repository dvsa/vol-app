<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class VehicleCheckboxTest extends MockeryTestCase
{
    public function testVehicleCheckboxUsesRegistration(): void
    {
        $config = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/licence-vehicles.table.php';
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn('table');

        $output = (new Checkbox($table))->render([
            'id' => 12,
            'vehicle' => ['vrm' => 'AB12 CDE'],
        ], $config['columns'][4]);

        self::assertStringContainsString('name="table[id][]" value="12"', $output);
        self::assertStringContainsString('id="table[id][12]"', $output);
        self::assertStringContainsString('for="table&#x5B;id&#x5D;&#x5B;12&#x5D;"', $output);
        self::assertStringContainsString('Select vehicle AB12 CDE</span>', $output);
    }
}
