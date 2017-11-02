<?php

/**
 * PresidingTc data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PresidingTc;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * PresidingTc data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PresidingTcTest extends AbstractDataServiceTestCase
{
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
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
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

        $sut = new PresidingTc();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchUserListData([]));
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
        $sut = new PresidingTc();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchUserListData([]);
    }
}
