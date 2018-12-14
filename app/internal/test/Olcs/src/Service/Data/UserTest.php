<?php

/**
 * User data service test
 *
 * @author someone <someone@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\User;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\User\UserList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * User data service test
 *
 * @author someone <someone@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UserTest extends AbstractDataServiceTestCase
{
    private $users = [
        ['id' => 1, 'loginId' => 'Logged in user'],
        ['id' => 5, 'loginId' => 'Mr E'],
    ];

    /**
     * Test fetchUserListData
     */
    public function testFetchUserListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'loginId',
            'order' => 'ASC',
            'isInternal' => true
        ];
        $team = 99;
        $dto = Qry::create($params);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params, $team) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($team, $dto->getTeam());
                    $this->assertEquals(true, $dto->getIsInternal());
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

        $sut = new User();
        $sut->setTeam($team);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchUserListData(['isInternal' => true]));
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
        $sut = new User();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchUserListData([]);
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptions()
    {
        $sut = new User();
        $sut->setData('userlist', $this->users);

        $this->assertEquals([1 => 'Logged in user', 5 => 'Mr E'], $sut->fetchListOptions([]));
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $sut = new User();
        $sut->setData('userlist', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }
}
