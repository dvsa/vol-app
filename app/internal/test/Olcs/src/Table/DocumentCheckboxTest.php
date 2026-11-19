<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Checkbox;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DocumentCheckboxTest extends MockeryTestCase
{
    #[DataProvider('tableNames')]
    public function testDocumentCheckboxUsesDescriptionOrFilename(string $name): void
    {
        $config = require __DIR__ . '/../../../../module/Olcs/src/Table/Tables/' . $name . '.table.php';
        $column = end($config['columns']);
        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getFieldset')->andReturn(null);
        $checkbox = new Checkbox($table);

        $first = $checkbox->render(['id' => 1, 'description' => 'Letter & notice', 'filename' => 'letter.pdf'], $column);
        $second = $checkbox->render(['id' => 2, 'filename' => 'folder/report.pdf'], $column);

        self::assertStringContainsString('name="id[]" value="1"', $first);
        self::assertStringContainsString('id="[id][1]"', $first);
        self::assertStringContainsString('Select document Letter &amp; notice</span>', $first);
        self::assertStringContainsString('name="id[]" value="2"', $second);
        self::assertStringContainsString('id="[id][2]"', $second);
        self::assertStringContainsString('Select document report.pdf</span>', $second);
    }

    public static function tableNames(): array
    {
        return [['documents'], ['documents-with-sla']];
    }
}
