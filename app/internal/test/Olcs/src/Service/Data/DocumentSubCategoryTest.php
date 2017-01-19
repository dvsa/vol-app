<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\DocumentSubCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList as Qry;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\DocumentSubCategory
 */
class DocumentSubCategoryTest extends AbstractDataServiceTestCase
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
                    $this->assertEquals(self::CAT_ID, $dto->getCategory());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $sut = new DocumentSubCategory();
        $sut->setCategory(self::CAT_ID);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }
}
