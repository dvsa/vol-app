<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * A view helper stand-in that is genuinely callable.
 *
 * Formatters reach helpers both ways — $helper(...) and $helper->render(...) — and neither a
 * closure nor a Mockery mock covers both. A closure has no render(); a mock of AbstractHelper is
 * not callable, because AbstractHelper does not declare __invoke and shouldReceive() cannot add
 * the magic method the engine looks for. This is a real class, so both work.
 */
final class HarnessViewHelper
{
    public function __invoke(mixed ...$arguments): string
    {
        return '';
    }

    public function render(mixed ...$arguments): string
    {
        return '';
    }

    public function __call(string $name, array $arguments): string
    {
        return '';
    }
}
