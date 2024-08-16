<?php

/**
 * User data service test
 *
 * @author someone <someone@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\User\UserList as Qry;
use Olcs\Service\Data\User;
use Mockery as m;

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

    /** @var User */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new User($this->abstractDataServiceServices);
    }

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

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params, $team) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($team, $dto->getTeam());
                    $this->assertEquals(true, $dto->getIsInternal());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setTeam($team);

        $this->assertEquals($results['results'], $this->sut->fetchUserListData(['isInternal' => true]));
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

        $this->sut->fetchUserListData([]);
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptions()
    {
        $this->sut->setData('userlist', $this->users);

        $this->assertEquals([1 => 'Logged in user', 5 => 'Mr E'], $this->sut->fetchListOptions([]));
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $this->sut->setData('userlist', false);

        $this->assertEquals([], $this->sut->fetchListOptions([]));
    }
}
