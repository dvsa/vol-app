<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use CommonTest\Common\Service\Table\Harness\TableRenderSnapshotTestCase;
use Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration;

/**
 * Table definitions live in the apps but the renderer lives in olcs-common, so the harness is
 * shared and each app points it at its own directories.
 */
final class TableRenderSnapshotTest extends TableRenderSnapshotTestCase
{
    /**
     * Renders every table in the app on purpose, so it takes longer than the 500ms the slow
     * test report allows a unit test. This limit only flags it getting much slower.
     */
    #[\Override]
    #[MaximumDuration(5000)]
    public function testRenderedOutputHasNotChanged(): void
    {
        parent::testRenderedOutputHasNotChanged();
    }

    #[\Override]
    protected function tableDirectories(): array
    {
        return [
            __DIR__ . '/../../../../module/Olcs/src/Table/Tables',
            __DIR__ . '/../../../../module/Admin/src/Table/Tables',
        ];
    }

    #[\Override]
    protected function snapshotFile(): string
    {
        return __DIR__ . '/table-render-snapshot.txt';
    }
}
