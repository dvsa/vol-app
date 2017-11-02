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

    /**
     * fetch the users list
     */
    public function listWithMockedServices() {
        $params = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'team' => 1
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->twice()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    if(is_a($dto,'Qry')) {
                        $this->assertEquals($params['sort'], $dto->getSort());
                        $this->assertEquals($params['order'], $dto->getOrder());
                        $this->assertEquals($params['team'], $dto->getTeam());
                    }
                    return 'query';
                }
            )
            ->getMock();
        $responseCounter = 1;
        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->isCallCountConstrained()
            ->andReturn(true)
            ->twice()
            ->shouldReceive('getResult')->twice()
            ->andReturnUsing(
                function () use (&$responseCounter) {
                    $results = [];
                    if ($responseCounter == 1) {
                        $results = ['results' => $this->userList];
                    }
                    if ($responseCounter == 2) {
                        $results = $this->currentUser;
                    }
                    $responseCounter++;

                    return $results;
                }
            )
            ->getMock();

        $sut = new AssignedToList();
        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, 2);

        return $sut;
    }

    /**
     * fetch the users list
     */
    public function listWithExceptionOnGetCurrentUser() {
        $params = [
            'sort' => 'p.forename',
            'order' => 'ASC',
            'team' => 1
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->twice()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    if(is_a($dto,'Qry')) {
                        $this->assertEquals($params['sort'], $dto->getSort());
                        $this->assertEquals($params['order'], $dto->getOrder());
                        $this->assertEquals($params['team'], $dto->getTeam());
                    }
                    return 'query';
                }
            )
            ->getMock();
        $responseCounter = 1;
        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->twice()
            ->andReturnUsing(
                function () use (&$responseCounter) {
                    return $responseCounter === 1;
                }
            )
            ->shouldReceive('getResult')->once()
            ->andReturnUsing(
                function () use (&$responseCounter) {
                    $results = $responseCounter === 1 ? ['results' => $this->userList] : [];
                    $responseCounter++;
                    return $results;
                }
            )
            ->getMock();

        $sut = new AssignedToList();
        $sut->setTeamId(1);
        $sut->setData('userList', $this->userList);
        $this->expectException(UnexpectedResponseException::class);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, 2);

        return $sut;
    }


    /**
     * test FetchListOptions using groups
     */
    public function testFetchListOptions()
    {

        $sut = $this->listWithMockedServices();

        $result = $sut->fetchListOptions();

        $expected = [
            $this->currentUser['id'] => $this->currentUser['contactDetails']['person']['forename'].' '.$this->currentUser['contactDetails']['person']['familyName'],
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
    public function testFetchListOptionsWithGroups()
    {

        $sut = $this->listWithMockedServices();

        $result = $sut->fetchListOptions([],true);

        $expected = [
            [
                'label' => null,
                'options' => [
                    $this->currentUser['id'] => $this->currentUser['contactDetails']['person']['forename'].' '.$this->currentUser['contactDetails']['person']['familyName'],
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

        $sut = $this->listWithExceptionOnGetCurrentUser();

        $sut->fetchListOptions();

    }

}
