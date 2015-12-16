<?php

/**
 * SubCategory Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * SubCategory Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SubCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'subCategoryName',
            'order' => 'ASC',
            'isScanCategory' => 'Y',
            'category' => 'cat'
        ];
        $dto = Qry::create($params);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['isScanCategory'], $dto->getIsScanCategory());
                    $this->assertEquals($params['category'], $dto->getCategory());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isServerError')
            ->andReturn(false)
            ->once()
            ->shouldReceive('isClientError')
            ->andReturn(false)
            ->once()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $sut = new SubCategory();
        $sut->setIsScanCategory('Y');
        $sut->setCategory('cat');
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }

    public function testFetchListDataWithException()
    {
        $this->setExpectedException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isServerError')
            ->andReturn(true)
            ->once()
            ->getMock();
        $sut = new SubCategory();
        $sut->setIsScanCategory('Y');
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, []);

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

    public function testGetDescriptionFromId()
    {
        $sut = new SubCategory();
        $sut->setData('all', [['subCategoryName' => 'test', 'id' => 4]]);

        $this->assertEquals('test', $sut->getDescriptionFromId(4));
        $this->assertNull($sut->getDescriptionFromId(123));
    }
}
