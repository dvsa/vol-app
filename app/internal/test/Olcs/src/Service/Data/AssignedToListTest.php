<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Olcs\Service\Data\AssignedToList;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as MyAccount;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;

class AssignedToListTest extends AbstractListDataServiceTestCase
{
    /** @var AssignedToList */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryA = m::mock(QueryContainerInterface::class);
        $this->queryB = m::mock(QueryContainerInterface::class);

        $this->sut = new AssignedToList($this->abstractListDataServiceServices);
    }

    private $userList = [
        [
            'id' => 5,
            'team' => [
                'id' => 1,
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
        ]
    ];

    private $currentUser = [
        'id' => 10,
        'contactDetails' => [
            'person' => [
                'forename' => 'John',
                'familyName' => 'Smith'
            ]
        ]
    ];

    private $userListParams = [
        'sort' => 'p.forename',
        'order' => 'ASC',
        'team' => 1
    ];

    public function mockTransferAnnotationBuilder()
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->twice()
            ->andReturnUsing(
                function ($dto) {
                    if (is_a($dto, 'Dvsa\Olcs\Transfer\Query\User\UserListInternal')) {
                        $this->assertEquals($this->userListParams['sort'], $dto->getSort());
                        $this->assertEquals($this->userListParams['order'], $dto->getOrder());
                        $this->assertEquals($this->userListParams['team'], $dto->getTeam());
                        return $this->queryA;
                    }
                    if (is_a($dto, 'Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount')) {
                        return $this->queryB;
                    }
                }
            );
    }

    public function mockTransferAnnotationBuilderForEmptyUserList()
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function ($dto) {
                    $this->assertTrue(is_a($dto, 'Dvsa\Olcs\Transfer\Query\User\UserListInternal'));
                    $this->assertEquals($this->userListParams['sort'], $dto->getSort());
                    $this->assertEquals($this->userListParams['order'], $dto->getOrder());
                    $this->assertEquals($this->userListParams['team'], $dto->getTeam());
                    return $this->queryA;
                }
            );
    }

    public function mockUserListResponse()
    {
        $mockUserListResponse = m::mock()
            ->shouldReceive('isOk')
            ->isCallCountConstrained()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['results' => $this->userList])
            ->getMock();

        return $mockUserListResponse;
    }

    public function mockUserListResponseEmpty()
    {
        $mockUserListResponse = m::mock()
            ->shouldReceive('isOk')
            ->isCallCountConstrained()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn([])
            ->getMock();

        return $mockUserListResponse;
    }

    /**
     * @return mixed
     */
    public function mockCurrentUserResponse()
    {
        $mockCurrentUserResponse = m::mock()
            ->shouldReceive('isOk')
            ->isCallCountConstrained()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($this->currentUser)
            ->getMock();
        return $mockCurrentUserResponse;
    }

    public function mockCurrentUserResponseIsOkFalse()
    {
        $mockCurrentUserResponseIsOkFalse = m::mock()
            ->shouldReceive('isOk')
            ->isCallCountConstrained()
            ->andReturn(false)
            ->getMock();
        return $mockCurrentUserResponseIsOkFalse;
    }


    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptions()
    {
        $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponse = $this->mockCurrentUserResponse();

        $this->sut->setTeamId(1);
        $this->sut->setData('userList', $this->userList);

        $this->mockHandleQuery($mockUserListResponse, $this->queryA);
        $this->mockHandleQuery($mockCurrentUserResponse, $this->queryB);

        $result = $this->sut->fetchListOptions();

        $expected = [
            $this->currentUser['id'] =>
                $this->currentUser['contactDetails']['person']['forename']
                .' '.$this->currentUser['contactDetails']['person']['familyName'],
            'unassigned' => 'Not assigned',
            'all' => 'All',
            5 => 'Paul Aldridge',
            6 => 'Adam Peterbottom'
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptionsEmptyUsers()
    {
        $this->mockTransferAnnotationBuilderForEmptyUserList();

        $mockUserListResponse = $this->mockUserListResponseEmpty();

        $this->sut->setTeamId(1);
        $this->sut->setData('userList', $this->userList);

        $this->mockHandleQuery($mockUserListResponse, $this->queryA);

        $result = $this->sut->fetchListOptions();

        $expected = [];

        $this->assertEquals($expected, $result);
    }

    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptionsWithGroups()
    {
        $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponse = $this->mockCurrentUserResponse();

        $this->sut->setTeamId(1);
        $this->sut->setData('userList', $this->userList);

        $this->mockHandleQuery($mockUserListResponse, $this->queryA);
        $this->mockHandleQuery($mockCurrentUserResponse, $this->queryB);

        $result = $this->sut->fetchListOptions([], true);

        $expected = [
            [
                'label' => null,
                'options' => [
                    $this->currentUser['id'] =>
                        $this->currentUser['contactDetails']['person']['forename']
                        .' '.$this->currentUser['contactDetails']['person']['familyName'],
                    'unassigned' => 'Not assigned',
                    'all' => 'All'
                ]
            ],
            [
                'label' => 'admin',
                'options' => [
                    5 => 'Paul Aldridge'
                ]
            ],
            [
                'label' => 'marketing',
                'options' => [
                    6 => 'Adam Peterbottom'
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptionsWithExceptionOnGetCurrentUser()
    {
        $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponseIsOkFalse = $this->mockCurrentUserResponseIsOkFalse();

        $this->sut->setTeamId(1);
        $this->sut->setData('userList', $this->userList);

        $this->expectException(DataServiceException::class);

        $this->mockHandleQuery($mockUserListResponse, $this->queryA);
        $this->mockHandleQuery($mockCurrentUserResponseIsOkFalse, $this->queryB);

        $this->sut->fetchListOptions();
    }
}
