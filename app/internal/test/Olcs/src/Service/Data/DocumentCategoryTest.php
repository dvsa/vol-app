<?php

/**
 * Document Category Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\DocumentCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Category\GetList as Qry;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Document Category Data Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
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
        $dto = Qry::create($params);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
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
        $sut->setIsScanCategory('Y');
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $results);

        $this->assertEquals($results['results'], $sut->fetchListData([]));
    }
}
