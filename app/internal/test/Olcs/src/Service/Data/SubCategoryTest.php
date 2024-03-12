<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList as Qry;
use Olcs\Service\Data\SubCategory;
use Mockery as m;

/**
 * @covers \Olcs\Service\Data\SubCategory
 */
class SubCategoryTest extends AbstractListDataServiceTestCase
{
    public const CAT_ID = 8001;

    /** @var SubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SubCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'subCategoryName',
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
                    $this->assertEquals(null, $dto->getIsDocCategory());
                    $this->assertEquals(null, $dto->getIsTaskCategory());
                    $this->assertEquals(self::CAT_ID, $dto->getCategory());
                    $this->assertEquals('Y', $dto->getIsOnlyWithItems());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setCategoryType(SubCategory::TYPE_IS_SCAN)
            ->setCategory(self::CAT_ID)
            ->setIsOnlyWithItems(true);

        $this->assertEquals($results['results'], $this->sut->fetchListData([]));
    }

    public function testFetchListDataCache()
    {
        $data = [
            [
                'id' => 9999,
                'subCategoryName'=> 'EXPECTED'
            ],
        ];
        $this->sut->setCategory(self::CAT_ID);
        $this->sut->setData(self::CAT_ID, $data);

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
            ->shouldReceive('isOk')->andReturn(false)->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setCategoryType(SubCategory::TYPE_IS_SCAN);

        $this->sut->fetchListData([]);
    }

    public function testFormatData()
    {
        $this->assertEquals(
            [
                1 => 'foo',
                2 => 'bar'
            ],
            $this->sut->formatData(
                [
                    [
                        'id' => 1,
                        'subCategoryName' => 'foo'
                    ], [
                        'id' => 2,
                        'subCategoryName' => 'bar'
                    ]
                ]
            )
        );
    }
}
