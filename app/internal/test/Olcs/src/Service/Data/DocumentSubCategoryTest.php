<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\DocumentSubCategory;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList as Qry;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;

/**
 * @covers \Olcs\Service\Data\DocumentSubCategory
 */
class DocumentSubCategoryTest extends AbstractListDataServiceTestCase
{
    const CAT_ID = 8001;

    /** @var DocumentSubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new DocumentSubCategory($this->abstractListDataServiceServices);
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
                    $this->assertEquals(self::CAT_ID, $dto->getCategory());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->sut->setCategory(self::CAT_ID);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData([]));
    }
}
