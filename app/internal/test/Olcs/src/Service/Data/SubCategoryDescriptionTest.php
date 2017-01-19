<?php

namespace OlcsTest\Service\Data;

use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\SubCategoryDescription;

/**
 * @covers \Olcs\Service\Data\SubCategoryDescription
 */
class SubCategoryDescriptionTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $subCategory = '9001';

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($subCategory) {
                    $this->assertEquals($subCategory, $dto->getSubCategory());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $sut = new SubCategoryDescription();
        $sut->setSubCategory($subCategory);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData());
    }

    public function testFetchListDataCache()
    {
        $subCategory = 8888;

        $data = [
            [
                'id' => 9999,
                'description'=> 'EXPECTED'
            ],
        ];
        $sut = new SubCategoryDescription();
        $sut->setSubCategory($subCategory);
        $sut->setData($subCategory, $data);

        static::assertEquals([9999 => 'EXPECTED'], $sut->fetchListOptions());
    }

    public function testFetchListDataWithException()
    {
        $this->setExpectedException(UnexpectedResponseException::class);
        $subCategory = 'subCategory';
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new SubCategoryDescription();
        $sut->setSubCategory($subCategory);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData([]);
    }
}
