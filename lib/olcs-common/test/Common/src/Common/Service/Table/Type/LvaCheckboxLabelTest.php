<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LvaCheckboxLabelTest extends MockeryTestCase
{
    #[DataProvider('rows')]
    public function testRowLabels(string $tableName, array $row, string $label): void
    {
        $config = require __DIR__ . '/../../../../../../../Common/src/Common/Table/Tables/' . $tableName . '.table.php';
        $column = end($config['columns']);
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn('table');
        $table->shouldReceive('isRowDisabled')->andReturn(false);

        $output = (new Checkbox($table))->render($row, $column);

        self::assertStringContainsString('name="table[id][]" value="7"', $output);
        self::assertStringContainsString('id="table[id][7]"', $output);
        self::assertStringContainsString('for="table&#x5B;id&#x5D;&#x5B;7&#x5D;"', $output);
        self::assertStringContainsString($label . '</span>', $output);
    }

    public static function rows(): array
    {
        return [
            ['lva-internal-vehicles', ['id' => 7, 'vehicle' => ['vrm' => 'AB12 CDE']], 'Select vehicle AB12 CDE'],
            ['lva-psv-vehicles', ['id' => 7, 'vehicle' => ['vrm' => 'XY34 FGH']], 'Select vehicle XY34 FGH'],
            ['lva-trailers', ['id' => 7, 'trailerNo' => 'TR123'], 'Select trailer TR123'],
            ['lva-psv-discs', ['id' => 7, 'discNo' => '12345'], 'Select disc 12345'],
            ['lva-community-licences', ['id' => 7, 'serialNoPrefix' => 'GB', 'issueNo' => 42], 'Select community licence GB, issue 42'],
            ['lva-transport-manangers', ['id' => 7, 'name' => ['forename' => 'Alex', 'familyName' => 'Smith']], 'Select transport manager Alex Smith'],
            ['lva-variation-transport-manangers', ['id' => 7, 'name' => ['forename' => 'Sam', 'familyName' => 'Jones']], 'Select transport manager Sam Jones'],
        ];
    }
}
