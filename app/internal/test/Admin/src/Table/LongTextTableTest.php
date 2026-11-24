<?php

declare(strict_types=1);

namespace AdminTest\Table;

use PHPUnit\Framework\TestCase;

final class LongTextTableTest extends TestCase
{
    public function testTableUsesTranslationKeyColumns(): void
    {
        $table = require __DIR__ . '/../../../../module/Admin/src/Table/Tables/admin-long-text.table.php';
        $names = array_column($table['columns'], 'name');

        self::assertContains('translationKey', $names);
        self::assertContains('description', $names);
        self::assertNotContains('referenceKey', $names);
        self::assertNotContains('pageName', $names);
    }
}
