<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table;

use CommonTest\Common\Service\Table\Harness\TableRenderSnapshotTestCase;

final class TableRenderSnapshotTest extends TableRenderSnapshotTestCase
{
    #[\Override]
    protected function tableDirectories(): array
    {
        return [__DIR__ . '/../../../../../../Common/src/Common/Table/Tables'];
    }

    #[\Override]
    protected function snapshotFile(): string
    {
        return __DIR__ . '/table-render-snapshot.txt';
    }
}
