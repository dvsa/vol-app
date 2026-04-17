<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Db\Service\Search\Indices\Person;
use Dvsa\Olcs\Db\Service\Search\Indices\Terms\TransportManagerLicenceStatus;
use Dvsa\Olcs\Db\Service\Search\QueryTemplate;
use Mockery as m;

/**
 * Class QueryTemplateTest
 */
class QueryTemplateTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testQueryTemplateMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $sut = new QueryTemplate('foo.json', 'bar');
        // prevent unused variable violation
        unset($sut);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('queryTemplateDataProvider')]
    public function testQueryTemplate(mixed $query, mixed $filters, mixed $filterTypes, mixed $dates, mixed $searchTypes, mixed $expected): void
    {
        if ($searchTypes === true) {
            $transportManagerLicenceStatus = m::mock(TransportManagerLicenceStatus::class);
            $transportManagerLicenceStatus->shouldReceive('applySearch')->once()->andReturnUsing(
                function (&$params) {
                    $params['apple'] = 'banana';
                }
            );

            $personSearchType = m::mock(Person::class);
            $personSearchType->shouldReceive('getFilters')->andReturn([$transportManagerLicenceStatus]);

            $searchTypes = [$personSearchType];
        }

        $sut = new QueryTemplate(__DIR__ . '/mock-query-template.json', $query, $filters, $filterTypes, $dates, $searchTypes);
        $this->assertEquals($expected, $sut->getParam('query'));
    }

    public static function queryTemplateDataProvider(): array
    {
        return [
            // simple query
            [
                'SMITH',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with special chars
            [
                'SM"\das\'[]{}ITH',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SM"\das\'[]{}ITH'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query empty
            [
                '',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => ''
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query single " (double quote)
            [
                '"',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => '"'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query double " (double quote)
            [
                '""',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => '""'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query ending with " (double quote)
            [
                '"SMITH"',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => '"SMITH"'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query json
            [
                '{"key":"value"}',
                [],
                [],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => '{"key":"value"}'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with one filter
            [
                'SMITH',
                [
                    'field_1' => 'value1',
                ],
                [
                    'field_1' => 'DYNAMIC',
                ],
                [],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'field_1' => 'value1'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with multiple filters
            [
                'SMITH',
                [
                    'field_1' => 'value1',
                    'field_2' => 'value2',
                    'field_3' => '0',
                    'field_4|field_5' => 'value3|value4',
                    'field_6' => 'value5',
                ],
                [
                    'field_1' => 'DYNAMIC',
                    'field_2' => 'DYNAMIC',
                    'field_3' => 'BOOLEAN',
                    'field_4|field_5' => 'FIXED',
                    'field_6' => 'COMPLEX',
                ],
                [],
                true,
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ],
                                'must' => [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'terms' => [
                                                    'field_4' => ['value3', 'value4'],
                                                ]
                                            ],
                                            [
                                                'terms' => [
                                                    'field_5' => ['value3', 'value4'],
                                                ]
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'field_1' => 'value1'
                                ]
                            ],
                            [
                                'term' => [
                                    'field_2' => 'value2'
                                ]
                            ],
                        ],
                        'must_not' => [
                            0 => [
                                'exists' => [
                                    'field' => 'field_3',
                                ]
                            ],
                        ],
                        'apple' => 'banana',
                    ]
                ]
            ],
            // query with from_and_to date range
            [
                'SMITH',
                [],
                [],
                [
                    'field_1_from_and_to' => '2010-09-30'
                ],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'field_1' => '2010-09-30'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with from only date range
            [
                'SMITH',
                [],
                [],
                [
                    'field_1_from' => '2010-09-30'
                ],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'range' => [
                                    'field_1' => ['from' => '2010-09-30']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with from and to date range
            [
                'SMITH',
                [],
                [],
                [
                    'field_1_from' => '2010-09-30',
                    'field_1_to' => '2010-10-30'
                ],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'range' => [
                                    'field_1' => ['from' => '2010-09-30', 'to' => '2010-10-30']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // query with filters and date ranges
            [
                'SMITH',
                [
                    'field_1' => 'value1',
                    'field_2' => 'value2',
                    'field_3' => 'value3',
                ],
                [
                    'field_1' => 'DYNAMIC',
                    'field_2' => 'DYNAMIC',
                    'field_3' => 'DYNAMIC',
                ],
                [
                    'field_1_from_and_to' => '2010-09-30',
                    'field_2_from' => '2010-09-30',
                    'field_3_from' => '2010-09-30',
                    'field_3_to' => '2010-10-30'
                ],
                [],
                [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'field_1' => 'SMITH'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'field_1' => 'value1'
                                ]
                            ],
                            [
                                'term' => [
                                    'field_2' => 'value2'
                                ]
                            ],
                            [
                                'term' => [
                                    'field_3' => 'value3'
                                ]
                            ],
                            [
                                'term' => [
                                    'field_1' => '2010-09-30'
                                ]
                            ],
                            [
                                'range' => [
                                    'field_2' => ['from' => '2010-09-30']
                                ]
                            ],
                            [
                                'range' => [
                                    'field_3' => ['from' => '2010-09-30', 'to' => '2010-10-30']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
