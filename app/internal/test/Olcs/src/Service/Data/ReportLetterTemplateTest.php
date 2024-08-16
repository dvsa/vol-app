<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Common\Service\Data\CategoryDataService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\DocTemplate\GetList;
use Olcs\Service\Data\ReportLetterTemplate;
use Mockery as m;

/**
 * Class ReportLetterTemplateTest Test
 */
class ReportLetterTemplateTest extends AbstractDataServiceTestCase
{
    /** @var ReportLetterTemplate */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ReportLetterTemplate($this->abstractDataServiceServices);
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(GetList::class))
            ->once()
            ->andReturnUsing(
                function ($dto) {
                    $this->assertEquals(CategoryDataService::CATEGORY_REPORT, $dto->getCategory());
                    $this->assertEquals(CategoryDataService::DOC_SUB_CATEGORY_LETTER, $dto->getSubCategory());
                    return $this->query;
                }
            );

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->once()
            ->withNoArgs()
            ->andReturn($results)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListOptions());
    }

    public function dpTestFetchListOptions()
    {
        return [
            'with data' => [
                'results' => [
                    'results' => [
                        [
                            'id' => 3,
                            'templateSlug' => 'sometemplateSlug',
                        ],
                        [
                            'id' => 43,
                            'templateSlug' => 'someothertemplateSlug',
                        ]
                    ]
                ],
                'expected' => [
                    'sometemplateSlug' => 'sometemplateSlug',
                    'someothertemplateSlug' => 'someothertemplateSlug',
                ]
            ],
            'no data' => [
                'results' => ['results' => []],
                'expected' => []
            ]
        ];
    }

    public function testFetchListOptionsWithException()
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(GetList::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListOptions(null);
    }
}
