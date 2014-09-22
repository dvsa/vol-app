<?php


namespace OlcsTest\View\Helper;

use Olcs\View\Helper\PiListData;

/**
 * Class PiListDataTest
 * @package OlcsTest\View\Helper
 */
class PiListDataTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provide__invoke
     * @param $input
     * @param $expected
     */
    public function test__invoke($input, $expected)
    {
        $sut = new PiListData();

        $this->assertEquals($expected, $sut($input));
    }

    public function provide__invoke()
    {
        return [
            [null, 'None selected'],
            [[], 'None selected'],
            [[['sectionCode' => 'a)', 'description'=>'desc']], 'a) desc'],
            [
                [
                    ['sectionCode' => 'a)', 'description'=>'desc'],
                    ['sectionCode' => 'b)', 'description'=>'desc']
                ],
                'a) desc, b) desc'
            ]
        ];
    }
}
 