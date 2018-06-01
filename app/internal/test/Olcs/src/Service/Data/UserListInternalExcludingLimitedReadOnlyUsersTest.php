<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers;
use Mockery as m;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

class UserListInternalExcludingLimitedReadOnlyUsersTest extends AbstractDataServiceTestCase
{
    private function getUsersWithoutLimitedReadOnly()
    {
        return [
            [
                'id' => 6,
                'team' => [
                    'id' => 2,
                    'name' => 'marketing'
                ],
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Adam',
                        'familyName' => 'Peterbottom'
                    ]
                ],
                'roles' => [
                    [
                        'role' => 'internal-admin',
                    ]

                ]
            ],

            [
                'id' => 10,
                'team' => [
                    'id' => 2,
                    'name' => 'unit_Team',
                ],
                'contactDetails' => [
                    'person' => [
                        'forename' => null,
                        'familyName' => null,
                    ],
                ],
                'roles' => [
                    [
                        'role' => 'internal-read-only',
                    ]

                ],
                'loginId' => 'usr888'
            ],
            [
                'id' => 11,
                'team' => [
                    'id' => 2,
                    'name' => 'unit_Team',
                ],
                'contactDetails' => [
                    'person' => [
                        'forename' => null,
                        'familyName' => null,
                    ],
                ],
                'roles' => [
                    [
                        'role' => 'internal-caseworker',
                    ]

                ],
                'loginId' => 'usr881'
            ],
        ];
    }

    private function limitedReadonlyUsers()
    {
        return [
            [
                'id' => 7,
                'team' => [
                    'id' => 6,
                    'name' => 'unit_Team',
                ],
                'contactDetails' => [
                    'person' => [
                        'forename' => null,
                        'familyName' => null,
                    ],
                ],
                'roles' => [
                    [
                        'role' => 'internal-limited-read-only',
                    ]

                ],
                'loginId' => 'usr884'
            ],
            [
                'id' => 5,
                'team' => [
                    'id' => 4,
                    'name' => 'admin'
                ],
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Paul',
                        'familyName' => 'Aldridge'
                    ],
                ],
                'roles' => [
                    ['role' => 'internal-admin'],
                    ['role' => 'internal-limited-read-only'],
                    ['role' => 'internal-case-worker'],
                ],
            ],
            [
                'id' => 9,
                'team' => [
                    'id' => 19,
                    'name' => 'admin'
                ],
                'loginId' => 'usr999',
                'roles' => [
                    ['role' => 'internal-admin'],
                    ['role' => 'internal-limited-read-only']
                ],
            ]
        ];
    }

    private function allUsers()
    {
        return array_merge($this->getUsersWithoutLimitedReadOnly(), $this->limitedReadonlyUsers());
    }

    /**
     * Test fetchUserListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->expectException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new UserListInternalExcludingLimitedReadOnlyUsers();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData();
    }

    public function testFetchListData()
    {
        $this->expectException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new UserListInternalExcludingLimitedReadOnlyUsers();

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData();
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsExcludeReadOnly()
    {
        $sut = new UserListInternalExcludingLimitedReadOnlyUsers();
        $sut->setData('userlist', $this->allUsers());

        $this->assertEquals(
            [
                6 => 'Adam Peterbottom',
                10 => 'usr888',
                11 => 'usr881',
            ],
            $sut->fetchListOptions([])
        );
    }

    public function testFetchListOptionsEmptyWhenNoData()
    {
        $sut = new UserListInternalExcludingLimitedReadOnlyUsers();

        $sut->setData('userlist', false);

        $this->assertEquals([], $sut->fetchListOptions());
    }

    public function testFetchListOptionWithUseGroups()
    {
        $sut = m::mock(UserListInternalExcludingLimitedReadOnlyUsers::class)
            ->makePartial();

        $sut->shouldReceive('formatDataForGroups')->with($this->getUsersWithoutLimitedReadOnly())->once();

        $sut->setData('userlist', $this->allUsers());

        $sut->fetchListOptions(null, true);
    }
}
