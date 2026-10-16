<?php

declare(strict_types=1);

namespace CommonTest\Common\Util;

use Common\Util\SafeRedirectUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SafeRedirectUrl::class)]
class SafeRedirectUrlTest extends TestCase
{
    #[DataProvider('safePathProvider')]
    public function testIsSafePath(bool $expected, ?string $candidate): void
    {
        $this->assertSame($expected, SafeRedirectUrl::isSafePath($candidate));
    }

    public static function safePathProvider(): array
    {
        return [
            'root relative' => [true, '/licence/7/documents/'],
            'root relative with query' => [true, '/licence/7/documents/?page=2'],
            'root relative with fragment' => [true, '/licence/7#section'],
            'bare slash' => [true, '/'],

            'null' => [false, null],
            'empty' => [false, ''],
            'absolute http' => [false, 'http://evil.example/x'],
            'absolute https' => [false, 'https://evil.example/x'],
            'protocol relative' => [false, '//evil.example/x'],
            'javascript scheme' => [false, 'javascript:alert(1)'],
            'data scheme' => [false, 'data:text/html,<script>alert(1)</script>'],
            'relative without leading slash' => [false, 'documents/'],

            // Browsers normalise backslashes in the authority to forward slashes, so these
            // are protocol-relative in practice even though they do not look it. An RFC 3986
            // parser reads every one of them as an ordinary path and calls it safe, which is
            // the reason this class does not delegate the decision to one — see its docblock.
            'backslash protocol relative' => [false, '/\\evil.example/x'],
            'double backslash' => [false, '\\\\evil.example/x'],
            'slash backslash slash' => [false, '/\\/evil.example/x'],

            // Leading whitespace and control characters are stripped before parsing.
            'leading space then scheme' => [false, ' javascript:alert(1)'],
            'leading tab then protocol relative' => [false, "\t//evil.example"],
            'embedded newline' => [false, "/licence\n//evil.example"],
            'embedded null byte' => [false, "/licence\0/x"],
        ];
    }

    #[DataProvider('safeForHostProvider')]
    public function testIsSafeForHost(bool $expected, ?string $candidate, ?string $host): void
    {
        $this->assertSame($expected, SafeRedirectUrl::isSafeForHost($candidate, $host));
    }

    public static function safeForHostProvider(): array
    {
        return [
            'same host absolute' => [true, 'https://vol.example/licence/7', 'vol.example'],
            'same host different case' => [true, 'https://VOL.example/licence/7', 'vol.example'],
            'same host http' => [true, 'http://vol.example/licence/7', 'vol.example'],
            'root relative still safe' => [true, '/licence/7', 'vol.example'],

            'different host' => [false, 'https://evil.example/x', 'vol.example'],
            'subdomain is a different host' => [false, 'https://evil.vol.example/x', 'vol.example'],
            'host as prefix' => [false, 'https://vol.example.evil.test/x', 'vol.example'],
            'userinfo confusion' => [false, 'https://vol.example@evil.example/x', 'vol.example'],
            'no current host' => [false, 'https://vol.example/x', null],
            'null candidate' => [false, null, 'vol.example'],
            'javascript scheme' => [false, 'javascript:alert(1)', 'vol.example'],
        ];
    }
}
