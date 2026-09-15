<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use CommonTest\Common\Service\Table\Harness\TableRenderSnapshotTestCase;

/**
 * Table definitions live in the apps but the renderer lives in olcs-common, so the harness is
 * shared and each app points it at its own directories.
 */
final class TableRenderSnapshotTest extends TableRenderSnapshotTestCase
{
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
