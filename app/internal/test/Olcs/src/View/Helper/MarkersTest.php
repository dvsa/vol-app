<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\Markers;

/**
 * Class MarkersTest
 * @package OlcsTest\View\Helper
 */
class MarkersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     */
    public function testInvoke($input, $expected)
    {
        $sut = new Markers();

        $result = $sut($input['markers'], $input['type']);
        if (isset($expected['count']) && $expected['count'] == 0) {
            $this->assertEquals($result, '');
        } elseif(isset($expected['contains'])) {
            $this->assertContains($expected['contains'], $result);
        }
    }

    public function provideInvoke()
    {
        return [
            [
                ['markers' => null, 'type' => null],
                ['count' => 0]
            ],
            [
                ['markers' => [], 'type' => ''],
                ['count' => 0]
            ],
            [
                [
                    'markers' =>
                        ['sometype' =>
                            [
                                0 => ['content' => 'foo']
                            ]
                        ],
                    'type' => 'sometype'
                ],
                ['count' => 1, 'contains' => 'foo'],
            ]
        ];
    }
}
