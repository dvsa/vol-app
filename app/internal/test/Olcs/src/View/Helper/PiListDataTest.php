<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\PiListData;

/**
 * Class PiListDataTest
 * @package OlcsTest\View\Helper
 */
class PiListDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvoke')]
    public function testInvoke(mixed $input, mixed $expected): void
    {
        $sut = new PiListData();

        $this->assertEquals($expected, $sut($input));
    }

    public static function provideInvoke(): array
    {
        return [
            [null, 'None selected'],
            [[], 'None selected'],
            [[['sectionCode' => 'a)', 'description' => 'desc']], 'a) desc'],
            [
                [
                    ['sectionCode' => 'a)', 'description' => 'desc'],
                    ['sectionCode' => 'b)', 'description' => 'desc']
                ],
                'a) desc, b) desc'
            ]
        ];
    }
}
