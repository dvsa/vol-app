<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use PHPUnit\Framework\TestCase;

final class BusRegistrationSelectionTest extends TestCase
{
    public function testDeleteSelectionLabel(): void
    {
        $table = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/bus-reg-variation-history.table.php';
        $render = $table['columns'][7]['formatter'];
        $output = $render(['id' => 7, 'regNo' => 'PB&123', 'canDelete' => true]);

        self::assertStringContainsString('id="bus-registration-variation-7" name="id" value="7"', $output);
        self::assertStringContainsString('for="bus-registration-variation-7">Delete PB&amp;123</label>', $output);
        self::assertNull($render(['id' => 8, 'regNo' => 'PB456']));
    }
}
