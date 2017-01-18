<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\DocumentCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Category\GetList as Qry;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\DocumentCategory
 */
class DocumentCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'description',
            'order' => 'ASC',
            'isDocCategory' => 'Y'
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['isDocCategory'], $dto->getIsDocCategory());
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
            ->once()
            ->getMock();

        $sut = new DocumentCategory();
        $sut->setCategoryType(DocumentCategory::TYPE_IS_DOC);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }
}
