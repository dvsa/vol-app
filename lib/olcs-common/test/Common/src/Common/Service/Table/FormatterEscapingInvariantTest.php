<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table;

use CommonTest\Common\Service\Table\Harness\FormatterEscapingInvariantTestCase;

/**
 * Formatters all live in olcs-common and are registered through one plugin config, so unlike the
 * table tests this exists once rather than once per app.
 */
final class FormatterEscapingInvariantTest extends FormatterEscapingInvariantTestCase
{
    #[\Override]
    protected function baselineFile(): string
    {
        return __DIR__ . '/formatter-escaping-baseline.txt';
    }
}
