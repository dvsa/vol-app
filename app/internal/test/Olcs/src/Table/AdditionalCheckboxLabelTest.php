<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AdditionalCheckboxLabelTest extends MockeryTestCase
{
    #[DataProvider('rows')]
    public function testRowLabels(string $module, string $tableName, array $row, string $label): void
    {
        $config = require __DIR__ . '/../../../../module/' . $module . '/src/Table/Tables/' . $tableName . '.table.php';
        $column = end($config['columns']);
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn(null);
        $table->shouldReceive('isRowDisabled')->andReturn(false);

        $output = (new Checkbox($table))->render($row, $column);

        self::assertStringContainsString('name="id[]" value="7"', $output);
        self::assertStringContainsString('id="[id][7]"', $output);
        self::assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;7&#x5D;"', $output);
        self::assertStringContainsString($label . '</span>', $output);
    }

    public static function rows(): array
    {
        return [
            ['Olcs', 'operator-people', ['id' => 7, 'forename' => 'Sam', 'familyName' => 'Jones'], 'Select person Sam Jones'],
            ['Admin', 'task-allocation-rules', [
                'id' => 7, 'category' => ['description' => 'Licence'], 'subCategory' => ['subCategoryName' => 'Review'],
                'team' => ['name' => 'Team A'],
            ], 'Select allocation rule Licence, Review, Team A'],
            ['Admin', 'admin-companies-house-alerts', [
                'id' => 7, 'companyOrLlpNo' => '12345678', 'licence' => ['licNo' => 'OB123'],
            ], 'Select Companies House alert 12345678, licence OB123'],
            ['Admin', 'admin-continuations', ['id' => 7, 'licence' => ['licNo' => 'OB123']], 'Select continuation for licence OB123'],
            ['Admin', 'admin-continuations-checklist', ['id' => 7, 'licence' => ['licNo' => 'OB123']], 'Select continuation for licence OB123'],
            ['Admin', 'admin-irfo-stock-control', ['id' => 7, 'serialNo' => '1234'], 'Select IRFO permit serial number 1234'],
            ['Admin', 'admin-irfo-psv-auth-continuation', ['id' => 7, 'organisation' => ['name' => 'Example']], 'Select authorisation 7, Example'],
            ['Admin', 'admin-irhp-permit-print', ['id' => 7, 'permitNumberWithPrefix' => 'P123'], 'Select permit P123'],
        ];
    }
}
