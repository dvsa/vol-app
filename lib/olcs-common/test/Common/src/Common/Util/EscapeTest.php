<?php

declare(strict_types=1);

namespace CommonTest\Util;

use Common\Util\Escape;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Util\Escape
 */
final class EscapeTest extends MockeryTestCase
{
    public function testHtml(): void
    {
        $actual = Escape::html('Aa &amp; <script');

        $this->assertEquals('Aa &amp;amp; &lt;script', $actual);
    }
}
