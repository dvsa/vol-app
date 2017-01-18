<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Category;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Category\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\Category
 */
class CategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'description',
            'order' => 'ASC',
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals('Y', $dto->getIsScanCategory());
                    $this->assertEquals('Y', $dto->getIsOnlyWithItems());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $sut = new Category();
        $sut->setCategoryType(Category::TYPE_IS_SCAN);
        $sut->setIsOnlyWithItems(true);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }

    public function testSetters()
    {
        $sut = new Category();

        static::assertNull($sut->getCategoryType());
        static::assertFalse($sut->getIsOnlyWithItems());

        $sut->setCategoryType(Category::TYPE_IS_DOC);
        $sut->setIsOnlyWithItems(true);

        static::assertEquals(Category::TYPE_IS_DOC, $sut->getCategoryType());
        static::assertTrue($sut->getIsOnlyWithItems());
    }

    public function testFetchListDataCache()
    {
        $data = [
            [
                'id' => 9999,
                'description'=> 'EXPECTED'
            ],
        ];
        $sut = new Category();
        $sut->setData('categories', $data);

        static::assertEquals([9999 => 'EXPECTED'], $sut->fetchListOptions());
    }

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

        $sut = new Category();
        $sut->setCategoryType(Category::TYPE_IS_SCAN);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData([]);
    }
}
