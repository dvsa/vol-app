<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Filter;

use Dvsa\Olcs\Transfer\Filter\Vrm;
use PHPUnit\Framework\TestCase;

final class VrmTest extends TestCase
{
    /**
     * @param $value
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('filterProvider')]
    public function testFilter(string $value, string $expected)
    {
        $sut = new Vrm();
        $this->assertEquals($expected, $sut->filter($value));
    }

    public static function filterProvider(): \Iterator
    {
        yield 'Lower-cased VRM' => [
            'abc123', 'ABC123'
        ];
        yield 'Translation of commonly mistyped / old plate #1' => [
            'GO', 'G0'
        ];
        yield 'Translation of commonly mistyped / old plate #2' => [
            'HSO', 'HS0'
        ];
        yield 'Prefixed whitespace' => [
            ' ABC123', 'ABC123'
        ];
        yield 'Prefixed and suffixed whitespace' => [
            ' ABC123 ', 'ABC123'
        ];
        yield 'Suffixed whitespace' => [
            'ABC123 ', 'ABC123'
        ];
        yield 'Space in between VRM' => [
            'AB12 3DE', 'AB123DE'
        ];
        yield 'Multiple spaces between VRM' => [
            'AB 1 2 3DE', 'AB123DE'
        ];
        yield 'Tab in between VRM' => [
            "AB12\t3DE", 'AB123DE'
        ];
        yield 'Lowercase, Spaces, Tabs and Newlines in VRM' => [
            "  A B\t\n1 2\t3\n d E\t", 'AB123DE'
        ];
    }
}
