<?php

declare(strict_types=1);

namespace CommonTest\Helper;

use Common\Helper\Str;
use Common\Test\MockeryTestCase;

/**
 * @see Str
 */
final class StrTest extends MockeryTestCase
{
    protected const string STRING_WITH_ANCHOR_TAG = '<a>Foo</a>';

    protected const string STRING_WITH_NO_HTML = 'foo bar baz';

    #[\PHPUnit\Framework\Attributes\Test]
    public function containsHtmlIsCallable(): void
    {
        $this->assertIsCallable(static fn(string $str): bool => \Common\Helper\Str::containsHtml($str));
    }

    #[\PHPUnit\Framework\Attributes\Depends('containsHtmlIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function containsHtmlReturnsFalseIfStringDoesNotContainHtml(): void
    {
        $this->assertFalse(Str::containsHtml(static::STRING_WITH_NO_HTML));
    }

    #[\PHPUnit\Framework\Attributes\Depends('containsHtmlIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function containsHtmlReturnsTrueIfStringContainsAnAnchor(): void
    {
        $this->assertTrue(Str::containsHtml(static::STRING_WITH_ANCHOR_TAG));
    }
}
