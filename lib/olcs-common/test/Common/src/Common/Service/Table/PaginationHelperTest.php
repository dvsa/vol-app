<?php

namespace CommonTest\Service\Table;

use Common\Service\Table\PaginationHelper;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Common\Service\Table\PaginationHelper
 */
class PaginationHelperTest extends MockeryTestCase
{
    /**
     * Test paginationHelper
     *
     * @dataProvider provider
     */
    public function testPaginationHelper($page, $total, $limit, $isSetTranslator, $expected): void
    {
        $mockTranslator = m::mock(\Dvsa\Olcs\Utils\Translation\TranslatorDelegator::class);
        $mockTranslator
            ->shouldReceive('translate')->with('pagination.next')->andReturn('TRNSLT_NEXT')
            ->shouldReceive('translate')->with('pagination.previous')->andReturn('TRNSLT_PREV');

        $paginationHelper = new PaginationHelper($page, $total, $limit);
        if ($isSetTranslator) {
            $paginationHelper->setTranslator($mockTranslator);
        }

        $this->assertEquals($expected, $paginationHelper->getOptions());
    }

    /**
     * Provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                'page' => 1,
                'total' => 10,
                'limit' => 10,
                'isSetTranslator' => false,
                'expect' => [
                    'previous' => [],
                    'links' => [
                        0 => [
                            'page' => 1,
                            'label' => 1,
                            'class' => PaginationHelper::CLASS_PAGINATION_ITEM_CURRENT,
                            'ariaCurrent' => 'aria-current="page"',
                        ],
                    ],
                    'next' => [],
                ],
            ],
            [
                'page' => 1,
                'total' => 50,
                'limit' => 10,
                'isSetTranslator' => true,
                'expect' => [
                    'previous' => [],
                    'links' => [
                        0 => [
                            'page' => 1,
                            'label' => 1,
                            'class' => PaginationHelper::CLASS_PAGINATION_ITEM_CURRENT,
                            'ariaCurrent' => 'aria-current="page"',
                        ],
                        1 => [
                            'page' => 2,
                            'label' => 2,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        2 => [
                            'page' => 3,
                            'label' => 3,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        3 => [
                            'page' => 4,
                            'label' => 4,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        4 => [
                            'page' => 5,
                            'label' => 5,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                    ],
                    'next' => [
                        'label' => 'TRNSLT_NEXT',
                        'page' => 2,
                    ],
                ],
            ],
            [
                'page' => 2,
                'total' => 50,
                'limit' => 10,
                'isSetTranslator' => true,
                'expect' => [
                    'previous' => [
                        'label' => 'TRNSLT_PREV',
                        'page' => 1,
                    ],
                    'links' => [
                        0 => [
                            'page' => 1,
                            'label' => 1,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        1 => [
                            'page' => 2,
                            'label' => 2,
                            'class' => PaginationHelper::CLASS_PAGINATION_ITEM_CURRENT,
                            'ariaCurrent' => 'aria-current="page"',
                        ],
                        2 => [
                            'page' => 3,
                            'label' => 3,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        3 => [
                            'page' => 4,
                            'label' => 4,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        4 => [
                            'page' => 5,
                            'label' => 5,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                    ],
                    'next' => [
                        'label' => 'TRNSLT_NEXT',
                        'page' => 3,
                    ],
                ],
            ],
            [
                'page' => 20,
                'total' => 1000,
                'limit' => 10,
                'isSetTranslator' => false,
                'expect' => [
                    'previous' => [
                        'label' => 'Previous',
                        'page' => 19,
                    ],
                    'links' => [
                        0 => [
                            'page' => 1,
                            'label' => 1,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        1 => [
                            'page' => null,
                            'label' => PaginationHelper::ELLIPSE,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        2 => [
                            'page' => 18,
                            'label' => 18,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        3 => [
                            'page' => 19,
                            'label' => 19,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        4 => [
                            'page' => 20,
                            'label' => 20,
                            'class' => PaginationHelper::CLASS_PAGINATION_ITEM_CURRENT,
                            'ariaCurrent' => 'aria-current="page"',
                        ],
                        5 => [
                            'page' => 21,
                            'label' => 21,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        6 => [
                            'page' => 22,
                            'label' => 22,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        7 => [
                            'page' => null,
                            'label' => PaginationHelper::ELLIPSE,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        8 => [
                            'page' => 100,
                            'label' => 100,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                    ],
                    'next' => [
                        'label' => 'Next',
                        'page' => 21,
                    ],
                ],
            ],
            [
                'page' => 100,
                'total' => 1000,
                'limit' => 10,
                'isSetTranslator' => true,
                'expect' => [
                    'previous' => [
                        'label' => 'TRNSLT_PREV',
                        'page' => 99,
                    ],
                    'links' => [
                        0 => [
                            'page' => 1,
                            'label' => 1,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        1 => [
                            'page' => null,
                            'label' => PaginationHelper::ELLIPSE,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        2 => [
                            'page' => 96,
                            'label' => 96,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        3 => [
                            'page' => 97,
                            'label' => 97,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        4 => [
                            'page' => 98,
                            'label' => 98,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        5 => [
                            'page' => 99,
                            'label' => 99,
                            'class' => '',
                            'ariaCurrent' => '',
                        ],
                        6 => [
                            'page' => 100,
                            'label' => 100,
                            'class' => PaginationHelper::CLASS_PAGINATION_ITEM_CURRENT,
                            'ariaCurrent' => 'aria-current="page"',
                        ],
                    ],
                    'next' => [],
                ],
            ],
        ];
    }
}
