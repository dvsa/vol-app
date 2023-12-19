<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Olcs\Service\Data\UserListInternal;
use Mockery as m;

/**
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 * @covers \Olcs\Service\Data\UserListInternal
 */
class UserListInternalTest extends AbstractListDataServiceTestCase
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

    /** @var UserListInternal */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new UserListInternal($this->abstractListDataServiceServices);
    }

    /**
     * Test fetchUserListData
     */
    public function testFetchUserListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'team' => 1,
            'excludeLimitedReadOnly' => false
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['team'], $dto->getTeam());
                    $this->assertEquals($params['excludeLimitedReadOnly'], $dto->getExcludeLimitedReadOnly());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setTeamId(1);

        $this->assertEquals($results['results'], $this->sut->fetchListData());

        // test cached results
        $this->assertEquals($results['results'], $this->sut->fetchListData());
    }

    /**
     * Test fetchUserListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListData();
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsUsingGroups()
    {
        $this->sut->setData('userlist', $this->userList);

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
            $this->sut->fetchListOptions([], true)
        );
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsWithoutGroups()
    {
        $this->sut->setData('userlist', $this->userList);

        // tests formatData is called to give the array structure below
        $this->assertSame(
            [
                5 => 'Paul Aldridge',
                9 => 'usr999',
                6 => 'Adam Peterbottom',
                7 => 'usr888',
            ],
            $this->sut->fetchListOptions([], false)
        );
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $this->sut->setData('userlist', false);

        $this->assertEquals([], $this->sut->fetchListOptions([]));
    }

    /**
     * Test set team
     */
    public function testSetTeamId()
    {
        $this->sut->setTeamId(1);
        $this->assertEquals(1, $this->sut->getTeamId());
    }

    public function testExcludeLimitedReadOnly()
    {
        $this->sut->setExcludeLimitedReadOnly(true);
        $this->assertTrue($this->sut->getExcludeLimitedReadOnly());
    }
}
