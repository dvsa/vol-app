<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Olcs\Service\Data\Category;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Category\GetList as Qry;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\Category
 */
class CategoryTest extends AbstractListDataServiceTestCase
{
    /** @var Category */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Category($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'description',
            'order' => 'ASC',
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals('Y', $dto->getIsScanCategory());
                    $this->assertEquals('Y', $dto->getIsOnlyWithItems());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->sut->setCategoryType(Category::TYPE_IS_SCAN);
        $this->sut->setIsOnlyWithItems(true);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData([]));
    }

    public function testSetters()
    {
        static::assertNull($this->sut->getCategoryType());
        static::assertFalse($this->sut->getIsOnlyWithItems());

        $this->sut->setCategoryType(Category::TYPE_IS_DOC);
        $this->sut->setIsOnlyWithItems(true);

        static::assertEquals(Category::TYPE_IS_DOC, $this->sut->getCategoryType());
        static::assertTrue($this->sut->getIsOnlyWithItems());
    }

    public function testFetchListDataCache()
    {
        $data = [
            [
                'id' => 9999,
                'description'=> 'EXPECTED'
            ],
        ];

        $this->sut->setData('categories', $data);

        static::assertEquals([9999 => 'EXPECTED'], $this->sut->fetchListOptions());
    }

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

        $this->sut->setCategoryType(Category::TYPE_IS_SCAN);

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListData([]);
    }
}
