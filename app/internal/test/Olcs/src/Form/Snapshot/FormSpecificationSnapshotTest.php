<?php

declare(strict_types=1);

namespace OlcsTest\Form\Snapshot;

use CommonTest\Common\Form\Snapshot\FormSpecificationSnapshotTestCase;

/**
 * Snapshots internal's forms and fieldsets. See FormSpecificationSnapshotTestCase.
 */
final class FormSpecificationSnapshotTest extends FormSpecificationSnapshotTestCase
{
    #[\Override]
    protected static function sourceDirectories(): array
    {
        return [
            __DIR__ . '/../../../../../module/Olcs/src',
            __DIR__ . '/../../../../../module/Admin/src',
        ];
    }

    #[\Override]
    protected static function snapshotDirectory(): string
    {
        return __DIR__ . '/forms';
    }
}
