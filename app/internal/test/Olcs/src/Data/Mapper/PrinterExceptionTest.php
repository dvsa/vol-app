<?php

/**
 * Printer Exception mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\PrinterException as Sut;
use Laminas\Form\FormInterface;

/**
 * Printer Exception mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterExceptionTest extends MockeryTestCase
{
    /**
     * @dataProvider fromResultProvider
     */
    public function testMapFromResult($data, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function fromResultProvider()
    {
        return [
            'data' => [
                [
                    'id' => 1,
                    'version' => 2,
                    'user' => ['id' => 3],
                    'team' => ['id' => 4],
                    'printer' => ['id' => 5],
                    'subCategory' => [
                        'id' => 6,
                        'category' => [
                            'id' => 7
                        ]
                    ]
                ],
                [
                    'exception-details' => [
                        'id' => 1,
                        'version' => 2,
                        'teamOrUser' => 'user',
                        'team' => 4
                    ],
                    'team-printer' => [
                        'printer' => 5,
                        'subCategoryTeam' => 6,
                        'categoryTeam' => 7
                    ],
                    'user-printer' => [
                        'user' => 3,
                        'printer' => 5,
                        'subCategoryUser' => 6,
                        'categoryUser' => 7
                    ]
                ]
            ],
            'no data' => [
                [
                    'team' => 1
                ],
                [
                    'exception-details' => [
                        'team' => 1
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider fromFormProvider
     */
    public function testMapFromForm($data, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function fromFormProvider()
    {
        return [
            'user' => [
                [
                    'exception-details' => [
                        'teamOrUser' => 'user',
                        'id' => 1,
                        'version' => 2,
                        'team' => 3
                    ],
                    'user-printer' => [
                        'user' => 'foo',
                        'subCategoryUser' => 'bar',
                        'printer' => 'cake'
                    ]
                ],
                [
                    'id' => 1,
                    'version' => 2,
                    'team' => 3,
                    'user' => 'foo',
                    'subCategory' => 'bar',
                    'printer' => 'cake'
                ]
            ],
            'team' => [
                [
                    'exception-details' => [
                        'teamOrUser' => 'team',
                        'id' => 1,
                        'version' => 2,
                        'team' => 3
                    ],
                    'team-printer' => [
                        'subCategoryTeam' => 'bar',
                        'printer' => 'cake'
                    ]
                ],
                [
                    'id' => 1,
                    'version' => 2,
                    'team' => 3,
                    'subCategory' => 'bar',
                    'printer' => 'cake'
                ]
            ]
        ];
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'subCategory' => 'foo',
                'printer' => 'bar',
                'user' => 'cake',
                'global' => 'baz'
            ]
        ];

        $expected = [
            'messages' => ['global' => 'baz']
        ];

        $formErrors = [
            'team-printer' => [
                'subCategoryTeam' => 'foo',
                'printer' => 'bar'
            ],
            'user-printer' => [
                'subCategoryUser' => 'foo',
                'printer' => 'bar',
                'user' => 'cake'
            ],
        ];

        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($formErrors)
            ->once()
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
