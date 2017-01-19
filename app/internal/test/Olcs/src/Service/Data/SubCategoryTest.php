<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\SubCategory
 */
class SubCategoryTest extends AbstractDataServiceTestCase
{
    const CAT_ID = 8001;

    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'subCategoryName',
            'order' => 'ASC',
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals('Y', $dto->getIsScanCategory());
                    $this->assertEquals(null, $dto->getIsDocCategory());
                    $this->assertEquals(null, $dto->getIsTaskCategory());
                    $this->assertEquals(self::CAT_ID, $dto->getCategory());
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

        $sut = (new SubCategory())
            ->setCategoryType(SubCategory::TYPE_IS_SCAN)
            ->setCategory(self::CAT_ID)
            ->setIsOnlyWithItems(true);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }

    public function testFetchListDataCache()
    {
        $data = [
            [
                'id' => 9999,
                'subCategoryName'=> 'EXPECTED'
            ],
        ];
        $sut = new SubCategory();
        $sut->setCategory(self::CAT_ID);
        $sut->setData(self::CAT_ID, $data);

        static::assertEquals([9999 => 'EXPECTED'], $sut->fetchListOptions());
    }

    public function testFetchListDataWithException()
    {
        $this->setExpectedException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(false)->once()
            ->getMock();

        $sut = new SubCategory();
        $sut->setCategoryType(SubCategory::TYPE_IS_SCAN);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData([]);
    }

    public function testFormatData()
    {
        $sut = new SubCategory();

        $this->assertEquals(
            [
                1 => 'foo',
                2 => 'bar'
            ],
            $sut->formatData(
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
