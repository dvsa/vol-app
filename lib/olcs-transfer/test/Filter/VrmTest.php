<?php

namespace Dvsa\OlcsTest\Transfer\Filter;

use Dvsa\Olcs\Transfer\Filter\Vrm;
use PHPUnit\Framework\TestCase;

class VrmTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     * @param $value
     * @param $expected
     */
    public function testFilter(string $value, string $expected)
    {
        $sut = new Vrm();
        $this->assertEquals($expected, $sut->filter($value));
    }

    public function filterProvider()
    {
        return [
            'Lower-cased VRM' => [
                'abc123', 'ABC123'
            ],
            'Translation of commonly mistyped / old plate #1' => [
                'GO', 'G0'
            ],
            'Translation of commonly mistyped / old plate #2' => [
                'HSO', 'HS0'
            ],
            'Prefixed whitespace' => [
                ' ABC123', 'ABC123'
            ],
            'Prefixed and suffixed whitespace' => [
                ' ABC123 ', 'ABC123'
            ],
            'Suffixed whitespace' => [
                'ABC123 ', 'ABC123'
            ],
            'Space in between VRM' => [
                'AB12 3DE', 'AB123DE'
            ],
            'Multiple spaces between VRM' => [
                'AB 1 2 3DE', 'AB123DE'
            ],
            'Tab in between VRM' => [
                "AB12\t3DE", 'AB123DE'
            ],
            'Lowercase, Spaces, Tabs and Newlines in VRM' => [
                "  A B\t\n1 2\t3\n d E\t", 'AB123DE'
            ],
        ];
    }
}
