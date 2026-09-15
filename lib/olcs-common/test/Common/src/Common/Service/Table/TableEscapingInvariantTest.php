<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table;

use CommonTest\Common\Service\Table\Harness\TableEscapingInvariantTestCase;

final class TableEscapingInvariantTest extends TableEscapingInvariantTestCase
{
    #[\Override]
    protected function tableDirectories(): array
    {
        return [__DIR__ . '/../../../../../../Common/src/Common/Table/Tables'];
    }

    #[\Override]
    protected function baselineFile(): string
    {
        return __DIR__ . '/table-escaping-baseline.txt';
    }
}
