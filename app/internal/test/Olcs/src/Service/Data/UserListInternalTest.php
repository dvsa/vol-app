<?php

/**
 * Internal user data service test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\UserListInternal;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Internal user data service test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UserInternalTest extends AbstractDataServiceTestCase
{
    /**
     * Test fetchUserListData
     */
    public function testFetchUserListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'p.forename',
            'order' => 'ASC'
        ];
        $dto = Qry::create($params);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertTrue($dto->getIsInternal());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $sut = new UserListInternal();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results['results'], $sut->fetchUserListData());
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
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, []);

        $sut->fetchUserListData();
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptions()
    {
        $userList = [
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
        $sut = new UserListInternal();
        $sut->setData('userlist', $userList);

        // tests team name order ASC followed by person forename ASC
        $this->assertEquals(
            [
                4 => [
                    'label' => 'admin',
                    'options' => [
                        5 => 'Paul Aldridge'
                    ]
                ],
                2 => [
                    'label' => 'marketing',
                    'options' => [
                        6 => 'Adam Peterbottom'
                    ]
                ]
            ],
            $sut->fetchListOptions([], true)
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
}
