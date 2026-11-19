<?php

declare(strict_types=1);

namespace AdminTest\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class PrinterCheckboxTest extends MockeryTestCase
{
    public function testPrinterRowsHaveDistinctLabelsAndIds(): void
    {
        $config = require __DIR__ . '/../../../../module/Admin/src/Table/Tables/admin-printers.table.php';
        $column = $config['columns'][2];
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn(null);
        $checkbox = new Checkbox($table);

        $first = $checkbox->render(['id' => 7, 'printerName' => 'Office'], $column);
        $second = $checkbox->render(['id' => 8, 'printerName' => 'Warehouse'], $column);

        self::assertStringContainsString('name="id[]" value="7"', $first);
        self::assertStringContainsString('id="[id][7]"', $first);
        self::assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;7&#x5D;"', $first);
        self::assertStringContainsString('Select printer Office</span>', $first);
        self::assertStringContainsString('name="id[]" value="8"', $second);
        self::assertStringContainsString('id="[id][8]"', $second);
        self::assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;8&#x5D;"', $second);
        self::assertStringContainsString('Select printer Warehouse</span>', $second);
    }
}
