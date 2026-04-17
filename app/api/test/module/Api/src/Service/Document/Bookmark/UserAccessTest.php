<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\UserAccess;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UserAccessTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new UserAccess();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testRenderWithNoUsers(): void
    {
        $bookmark = new UserAccess();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithUsers(): void
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->andReturnUsing(fn($text) => $text . '_translated')
            ->getMock();

        $bookmark = m::mock(UserAccess::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_3CELL_TABLE')
            ->andReturn('tableSnippet')
            ->once()
            ->shouldReceive('getSnippet')
            ->with('UserAccess')
            ->andReturn('userAccessSnippet')
            ->once()
            ->shouldReceive('getTranslator')
            ->andReturn($mockTranslator)
            ->getMock();

        $bookmark->setData(
            [
                'licNo' => 1234,
                'organisation' => [
                    'organisationUsers' => [
                        [
                            'isAdministrator' => 'N',
                            'user' => [
                                'contactDetails' =>
                                    [
                                        'emailAddress' => 'test2@test.com',
                                        'person' =>
                                            [
                                                'familyName' => 'Test2',
                                                'forename' => 'Test2',
                                            ],
                                    ],
                                'roles' =>
                                    [
                                        [
                                            'role' => 'operator',
                                        ],
                                    ],
                            ]
                        ],
                        [
                            'isAdministrator' => 'Y',
                            'user' => [
                                'contactDetails' =>
                                    [
                                        'emailAddress' => 'test1@test.com',
                                        'person' =>
                                            [
                                                'familyName' => 'Test1',
                                                'forename' => 'Test1',
                                            ],
                                    ],
                                'roles' =>
                                    [
                                        [
                                            'role' => 'admin',
                                        ],
                                    ],
                            ]
                        ],
                        [
                            'isAdministrator' => 'Y',
                            'user' => [
                                'contactDetails' =>
                                    [
                                        'emailAddress' => 'test3@test.com',
                                        'person' =>
                                            [
                                                'familyName' => 'Test3',
                                                'forename' => 'Test3',
                                            ],
                                    ],
                                'roles' =>
                                    [
                                        [
                                            'role' => 'admin',
                                        ],
                                    ],
                            ]
                        ],
                        [
                            'isAdministrator' => 'Y',
                            'user' => [
                                'contactDetails' =>
                                    [
                                        'emailAddress' => 'test33@test.com',
                                        'person' =>
                                            [
                                                'familyName' => 'Test3',
                                                'forename' => 'Test3',
                                            ],
                                    ],
                                'roles' =>
                                    [
                                        [
                                            'role' => 'admin',
                                        ],
                                    ],
                            ]
                        ]
                    ]
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'Name',
            'BOOKMARK2' => 'Email address',
            'BOOKMARK3' => 'Permission'
        ];
        $row1 = [
            'BOOKMARK1' => 'Test1 Test1',
            'BOOKMARK2' => 'test1@test.com',
            'BOOKMARK3' => 'role.admin_translated'
        ];
        $row2 = [
            'BOOKMARK1' => 'Test2 Test2',
            'BOOKMARK2' => 'test2@test.com',
            'BOOKMARK3' => 'role.operator_translated'
        ];
        $row3 = [
            'BOOKMARK1' => 'Test3 Test3',
            'BOOKMARK2' => 'test3@test.com',
            'BOOKMARK3' => 'role.admin_translated'
        ];
        $row4 = [
            'BOOKMARK1' => 'Test3 Test3',
            'BOOKMARK2' => 'test33@test.com',
            'BOOKMARK3' => 'role.admin_translated'
        ];

        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => '',
            'BOOKMARK3' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('tableSnippet', $header)
            ->andReturn('header|')
            ->once()
            ->shouldReceive('replace')
            ->with('tableSnippet', $row1)
            ->andReturn('row1|')
            ->once()
            ->shouldReceive('replace')
            ->with('tableSnippet', $row2)
            ->andReturn('row2|')
            ->once()
            ->shouldReceive('replace')
            ->with('tableSnippet', $row3)
            ->andReturn('row3|')
            ->once()
            ->shouldReceive('replace')
            ->with('tableSnippet', $row4)
            ->andReturn('row4|')
            ->once()
            ->shouldReceive('replace')
            ->with('tableSnippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->times(11)
            ->shouldReceive('replace')
            ->with(
                'userAccessSnippet',
                [
                    'LICENCE_NUMBER' => 1234,
                    'SELF_SERVE_MESSAGE' => "You can log in and select 'Manage users' to amend the current users.",
                    'USERS_TABLE' => 'header|row1|row2|row3|row4|' . str_repeat('emptyrow|', 11)
                ]
            )
            ->andReturn('UserAccessPageContent')
            ->getMock();

        $bookmark->setParser($mockParser);

        $this->assertEquals(
            'UserAccessPageContent',
            $bookmark->render()
        );
    }

    /**
     *
     * @param $isAdministrator
     * @param $expectedMessage
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('selfServeMessageDataProvider')]
    public function testSelfServeMessage(mixed $isAdministrator, mixed $expectedMessage): void
    {
        $bookmark = m::mock(UserAccess::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('UserAccess')
            ->andReturn('snippet')
            ->shouldReceive('generateUserTable')
            ->andReturn('table')
            ->getMock();

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with(
                'snippet',
                [
                    'LICENCE_NUMBER' => 1234,
                    'SELF_SERVE_MESSAGE' => $expectedMessage,
                    'USERS_TABLE' => 'table'
                ]
            )
            ->andReturn('replacedstring')
            ->once()
            ->getMock();

        $data = [
            'licNo' => 1234,
            'organisation' => [
                'organisationUsers' => [
                    [
                        'isAdministrator' => $isAdministrator
                    ]
                ]
            ]
        ];

        $bookmark->setData($data);
        $bookmark->setParser($mockParser);

        $this->assertEquals('replacedstring', $bookmark->render());
    }

    public static function selfServeMessageDataProvider(): array
    {
        return [
            [
                'Y',
                "You can log in and select 'Manage users' to amend the current users."
            ],
            [
                'N',
                "You can register for a self-serve account to amend your licence details online."
            ]
        ];
    }
}
