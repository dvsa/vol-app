<?php

/**
 * PresidingTc data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Olcs\Service\Data\PresidingTc;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as Qry;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;

/**
 * PresidingTc data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PresidingTcTest extends AbstractDataServiceTestCase
{
    /** @var PresidingTc */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PresidingTc($this->abstractDataServiceServices);
    }

    /**
     * Test fetchUserListData
     */
    public function testFetchUserListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'name',
            'order' => 'ASC'
        ];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
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

        $this->assertEquals($results['results'], $this->sut->fetchUserListData([]));
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
}
