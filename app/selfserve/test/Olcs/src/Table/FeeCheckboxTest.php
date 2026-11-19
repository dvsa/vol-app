<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class FeeCheckboxTest extends MockeryTestCase
{
    public function testFeeRowsHaveDescriptiveLabelsAndPreserveSelection(): void
    {
        $config = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/fees.table.php';
        $column = $config['columns'][3];
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn(null);
        $checkbox = new Checkbox($table);

        $first = $checkbox->render([
            'id' => 7,
            'description' => 'Application & variation',
            'licence' => ['licNo' => 'OB123'],
            'outstanding' => 123.45,
            'isExpiredForLicence' => false,
        ], $column);
        $second = $checkbox->render([
            'id' => 8,
            'description' => 'Continuation',
            'licence' => ['licNo' => 'OB456'],
            'outstanding' => 50,
            'isExpiredForLicence' => true,
        ], $column);

        self::assertStringContainsString('name="id[]" value="7"', $first);
        self::assertStringContainsString('id="[id][7]"', $first);
        self::assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;7&#x5D;"', $first);
        self::assertStringContainsString('Select fee Application &amp; variation, OB123, £123.45 outstanding', $first);
        self::assertStringNotContainsString('disabled="disabled"', $first);
        self::assertStringContainsString('name="id[]" value="8"', $second);
        self::assertStringContainsString('id="[id][8]"', $second);
        self::assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;8&#x5D;"', $second);
        self::assertStringContainsString('Select fee Continuation, OB456, £50.00 outstanding', $second);
        self::assertStringContainsString('disabled="disabled"', $second);
    }
}
