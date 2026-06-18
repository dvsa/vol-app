<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\Replacements;
use PHPUnit\Framework\TestCase;

class ReplacementsTest extends TestCase
{
    public function testApplyReplacesAllTokens(): void
    {
        $replacements = new Replacements([
            '{{foo}}' => 'bar',
            '{{bar}}' => 'foo',
        ]);

        $this->assertEquals('bar and foo', $replacements->apply('{{foo}} and {{bar}}'));
    }

    public function testApplyLeavesMessageUnchangedWhenMapIsEmpty(): void
    {
        $replacements = new Replacements([]);

        $this->assertEquals('a {{token}} stays', $replacements->apply('a {{token}} stays'));
    }

    public function testApplyLeavesNonMatchingTokensUntouched(): void
    {
        $replacements = new Replacements(['{{foo}}' => 'bar']);

        $this->assertEquals('bar and {{unknown}}', $replacements->apply('{{foo}} and {{unknown}}'));
    }

    public function testToArrayReturnsTheConfiguredMap(): void
    {
        $map = ['{{a}}' => '1', '{{b}}' => '2'];

        $this->assertEquals($map, (new Replacements($map))->toArray());
    }
}
