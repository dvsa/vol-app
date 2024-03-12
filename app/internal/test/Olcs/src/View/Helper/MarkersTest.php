<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\Markers;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class MarkersTest
 * @package OlcsTest\View\Helper
 */
class MarkersTest extends MockeryTestCase
{
    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     */
    public function testInvoke($input, $expected)
    {
        $sut = new Markers();

        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);
        $mockViewHelper = m::mock(\Laminas\View\Helper\Url::class);
        $mockViewHelper->shouldReceive('__invoke');
        $mockView->shouldReceive('plugin')->andReturn($mockViewHelper);

        $sut->setView($mockView);

        $result = $sut($input['markers'], $input['type']);
        if (isset($expected['count']) && $expected['count'] == 0) {
            $this->assertEquals($result, '');
        } else {
            // count individual markers
            $this->assertEquals($expected['count'], substr_count($result, 'notice--warning'));
        }

        if (isset($expected['contains'])) {
            $this->assertStringContainsString($expected['contains'], $result);
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
            ],
            [
                [
                    'markers' =>
                        ['sometype' =>
                            [
                                0 => ['content' => 'bar'],
                                1 => ['content' => 'bar'],
                                2 => ['content' => 'bar']
                            ]
                        ],
                    'type' => 'sometype'
                ],
                ['count' => 3, 'contains' => 'bar'],
            ],
            [
                [
                    'markers' =>
                        ['sometype' =>
                            [
                                0 => [
                                    'content' => 'bar %s', 'data' => [
                                        0 => [
                                            'linkText' => 'blah',
                                            'type' => 'url',
                                            'route' => 'case',
                                            'params' => [
                                                'case' => 1
                                            ]
                                        ]
                                    ]
                                ],
                            ]
                        ],
                    'type' => 'sometype'
                ],
                ['count' => 1, 'contains' => 'blah'],
            ],
            [
                [
                    'markers' =>
                        ['sometype' =>
                            [
                                0 => [
                                    'content' => 'bar %s', 'data' => null
                                ],
                            ]
                        ],
                    'type' => 'sometype'
                ],
                ['count' => 1, 'contains' => 'bar %s'],
            ]
        ];
    }
}
