<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\SubCategoryDescription\GetList as Qry;
use Mockery as m;
use Olcs\Service\Data\SubCategoryDescription;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\SubCategoryDescription::class)]
class SubCategoryDescriptionTest extends AbstractListDataServiceTestCase
{
    /** @var SubCategoryDescription */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SubCategoryDescription($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];
        $subCategory = '9001';

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($subCategory) {
                    $this->assertEquals($subCategory, $dto->getSubCategory());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setSubCategory($subCategory);

        $this->assertEquals($results['results'], $this->sut->fetchListData());
    }

    public function testFetchListDataCache(): void
    {
        $subCategory = 8888;

        $data = [
            [
                'id' => 9999,
                'description' => 'EXPECTED'
            ],
        ];

        $this->sut->setSubCategory($subCategory);
        $this->sut->setData($subCategory, $data);

        static::assertEquals([9999 => 'EXPECTED'], $this->sut->fetchListOptions());
    }

    public function testFetchListDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $subCategory = 'subCategory';

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setSubCategory($subCategory);

        $this->sut->fetchListData([]);
    }
}
