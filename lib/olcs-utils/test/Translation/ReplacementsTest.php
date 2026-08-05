<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\Replacements;
use PHPUnit\Framework\TestCase;

final class ReplacementsTest extends TestCase
{
    public function testApplyReplacesAllTokens(): void
    {
        $replacements = new Replacements([
            '{{foo}}' => 'bar',
            '{{bar}}' => 'foo',
        ]);

        $this->assertSame('bar and foo', $replacements->apply('{{foo}} and {{bar}}'));
    }

    public function testApplyLeavesMessageUnchangedWhenMapIsEmpty(): void
    {
        $replacements = new Replacements([]);

        $this->assertSame('a {{token}} stays', $replacements->apply('a {{token}} stays'));
    }

    public function testApplyLeavesNonMatchingTokensUntouched(): void
    {
        $replacements = new Replacements(['{{foo}}' => 'bar']);

        $this->assertSame('bar and {{unknown}}', $replacements->apply('{{foo}} and {{unknown}}'));
    }

    public function testToArrayReturnsTheConfiguredMap(): void
    {
        $map = ['{{a}}' => '1', '{{b}}' => '2'];

        $this->assertSame($map, new Replacements($map)->toArray());
    }
}
