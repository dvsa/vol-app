<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\PiListData;

/**
 * Class PiListDataTest
 * @package OlcsTest\View\Helper
 */
class PiListDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     */
    public function testInvoke($input, $expected)
    {
        $sut = new PiListData();

        $this->assertEquals($expected, $sut($input));
    }

    public function provideInvoke()
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
