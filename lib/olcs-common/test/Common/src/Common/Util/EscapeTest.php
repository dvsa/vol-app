<?php

namespace CommonTest\Util;

use Common\Util\Escape;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Util\Escape
 */
class EscapeTest extends MockeryTestCase
{
    public function testHtml(): void
    {
        $actual = Escape::html('Aa &amp; <script');

        static::assertEquals('Aa &amp;amp; &lt;script', $actual);
    }
}
