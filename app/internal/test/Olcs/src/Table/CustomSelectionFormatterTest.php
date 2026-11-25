<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use PHPUnit\Framework\TestCase;

final class CustomSelectionFormatterTest extends TestCase
{
    public function testCandidatePermitCheckbox(): void
    {
        $table = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/irhp-permits-ecmt-candidate-partial-select.table.php';
        $render = $table['columns'][3]['formatter'];
        $selected = $render(['id' => 7, 'permitNumber' => 'P&123', 'wanted' => true]);
        $unselected = $render(['id' => 8, 'permitNumber' => 'P456', 'wanted' => false]);

        self::assertStringContainsString('id="candidate-permit-7" name="id[]" value="7" checked', $selected);
        self::assertStringContainsString('for="candidate-permit-7">Select permit P&amp;123</label>', $selected);
        self::assertStringContainsString('id="candidate-permit-8" name="id[]" value="8"', $unselected);
        self::assertStringNotContainsString('checked', $unselected);
    }

    public function testLatestRegistrationRadio(): void
    {
        $table = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/Bus/registration-history.table.php';
        $render = $table['columns'][7]['formatter'];
        $row = ['id' => 7, 'regNo' => 'PB&123', 'variationNo' => 2, 'isLatestVariation' => true];
        $output = $render($row);

        self::assertStringContainsString('id="bus-registration-variation-7" name="id" value="7"', $output);
        self::assertStringContainsString('for="bus-registration-variation-7">Select registration PB&amp;123, variation 2</label>', $output);
        $row['isLatestVariation'] = false;
        self::assertNull($render($row));
    }
}
