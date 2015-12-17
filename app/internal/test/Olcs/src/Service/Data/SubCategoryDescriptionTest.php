<?php

/**
 * SubCategoryDescription Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubCategoryDescription;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\SubCategoryDescription\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * SubCategoryDescription Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SubCategoryDescriptionTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $subCategory = 'subCat';
        $dto = Qry::create([]);
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
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $sut = new SubCategoryDescription();
        $sut->setSubCategory($subCategory);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
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
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, []);

        $sut->fetchListData([]);
    }

    public function testGetDescriptionFromId()
    {
        $sut = new SubCategoryDescription();
        $sut->setData('all', [['description' => 'test', 'id' => 4]]);

        $this->assertEquals('test', $sut->getDescriptionFromId(4));
        $this->assertNull($sut->getDescriptionFromId(123));
    }
}
