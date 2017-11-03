<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\AssignedToList;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as MyAccount;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

class AssignedToListTest extends AbstractDataServiceTestCase
{
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
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->twice()
            ->andReturnUsing(
                function ($dto) {
                    if (is_a($dto, 'Dvsa\Olcs\Transfer\Query\User\UserListInternal')) {
                        $this->assertEquals($this->userListParams['sort'], $dto->getSort());
                        $this->assertEquals($this->userListParams['order'], $dto->getOrder());
                        $this->assertEquals($this->userListParams['team'], $dto->getTeam());
                        return 'queryA';
                    }
                    if (is_a($dto, 'Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount')) {
                        return 'queryB';
                    }
                }
            )
            ->getMock();

        return $mockTransferAnnotationBuilder;
    }

    public function mockTransferAnnotationBuilderForEmptyUserList()
    {
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function ($dto) {
                    $this->assertTrue(is_a($dto, 'Dvsa\Olcs\Transfer\Query\User\UserListInternal'));
                    $this->assertEquals($this->userListParams['sort'], $dto->getSort());
                    $this->assertEquals($this->userListParams['order'], $dto->getOrder());
                    $this->assertEquals($this->userListParams['team'], $dto->getTeam());
                    return 'queryA';
                }
            )
            ->getMock();

        return $mockTransferAnnotationBuilder;
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

        $mockTransferAnnotationBuilder = $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponse = $this->mockCurrentUserResponse();

        $sut = new AssignedToList();

        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->setupQuerySender($sut, $mockTransferAnnotationBuilder);
        $this->mockHandleSingleQuery($mockUserListResponse, 'queryA');
        $this->mockHandleSingleQuery($mockCurrentUserResponse, 'queryB');

        $result = $sut->fetchListOptions();

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

        $mockTransferAnnotationBuilder = $this->mockTransferAnnotationBuilderForEmptyUserList();

        $mockUserListResponse = $this->mockUserListResponseEmpty();

        $sut = new AssignedToList();

        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->setupQuerySender($sut, $mockTransferAnnotationBuilder);
        $this->mockHandleSingleQuery($mockUserListResponse, 'queryA');

        $result = $sut->fetchListOptions();

        $expected = [];

        $this->assertEquals($expected, $result);
    }

    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptionsWithGroups()
    {

        $mockTransferAnnotationBuilder = $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponse = $this->mockCurrentUserResponse();

        $sut = new AssignedToList();

        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->setupQuerySender($sut, $mockTransferAnnotationBuilder);
        $this->mockHandleSingleQuery($mockUserListResponse, 'queryA');
        $this->mockHandleSingleQuery($mockCurrentUserResponse, 'queryB');

        $result = $sut->fetchListOptions([], true);

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

        $mockTransferAnnotationBuilder = $this->mockTransferAnnotationBuilder();

        $mockUserListResponse = $this->mockUserListResponse();

        $mockCurrentUserResponseIsOkFalse = $this->mockCurrentUserResponseIsOkFalse();

        $sut = new AssignedToList();
        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->expectException(UnexpectedResponseException::class);
        $this->setupQuerySender($sut, $mockTransferAnnotationBuilder);
        $this->mockHandleSingleQuery($mockUserListResponse, 'queryA');
        $this->mockHandleSingleQuery($mockCurrentUserResponseIsOkFalse, 'queryB');

        $sut->fetchListOptions();

    }
}
