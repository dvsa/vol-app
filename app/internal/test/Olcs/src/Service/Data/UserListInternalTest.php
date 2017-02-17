<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\UserListInternal;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\UserListInternal
 */
class UserListInternalTest extends AbstractDataServiceTestCase
{
    private $userList = [
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
                ]
            ]
        ],
        [
            'id' => 9,
            'team' => [
                'id' => 4,
                'name' => 'admin'
            ],
            'loginId' => 'usr999'
        ],
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
            ]
        ],
        [
            'id' => 7,
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
            'loginId' => 'usr888'
        ],
    ];

    /**
     * Test fetchUserListData
     */
    public function testFetchUserListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'team' => 1
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['team'], $dto->getTeam());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $sut = new UserListInternal();
        $sut->setTeamId(1);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData());

        // test cached results
        $this->assertEquals($results['results'], $sut->fetchListData());
    }

    /**
     * Test fetchUserListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->setExpectedException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new UserListInternal();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData();
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsUsingGroups()
    {
        $sut = new UserListInternal();
        $sut->setData('userlist', $this->userList);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [
                4 => [
                    'label' => 'admin',
                    'options' => [
                        5 => 'Paul Aldridge',
                        9 => 'usr999'
                    ]
                ],
                2 => [
                    'label' => 'marketing',
                    'options' => [
                        6 => 'Adam Peterbottom',
                        7 => 'usr888',
                    ]
                ]
            ],
            $sut->fetchListOptions([], true)
        );
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsWithoutGroups()
    {
        $sut = new UserListInternal();
        $sut->setData('userlist', $this->userList);

        // tests formatData is called to give the array structure below
        $this->assertSame(
            [
                5 => 'Paul Aldridge',
                9 => 'usr999',
                6 => 'Adam Peterbottom',
                7 => 'usr888',
            ],
            $sut->fetchListOptions([], false)
        );
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $sut = new UserListInternal();
        $sut->setData('userlist', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }

    /**
     * Test set team
     */
    public function testSetTeamId()
    {
        $sut = new UserListInternal();
        $sut->setTeamId(1);
        $this->assertEquals($sut->getTeamId(), 1);
    }
}
