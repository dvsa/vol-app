<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\DocumentCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Category\GetList as Qry;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\DocumentCategory
 */
class DocumentCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var DocumentCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DocumentCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'description',
            'order' => 'ASC',
            'isDocCategory' => 'Y'
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['isDocCategory'], $dto->getIsDocCategory());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->sut->setCategoryType(DocumentCategory::TYPE_IS_DOC);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData([]));
    }
}
