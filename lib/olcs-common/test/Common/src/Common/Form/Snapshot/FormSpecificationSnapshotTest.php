<?php

declare(strict_types=1);

namespace CommonTest\Common\Form\Snapshot;

/**
 * Snapshots olcs-common's forms and fieldsets. See FormSpecificationSnapshotTestCase.
 */
final class FormSpecificationSnapshotTest extends FormSpecificationSnapshotTestCase
{
    #[\Override]
    protected static function sourceDirectories(): array
    {
        return [__DIR__ . '/../../../../../../Common/src'];
    }

    #[\Override]
    protected static function snapshotDirectory(): string
    {
        return __DIR__ . '/forms';
    }
}
