<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Common\Service\Data\CategoryDataService;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\DocTemplate\GetList;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Olcs\Service\Data\ReportLetterTemplate;
use Mockery as m;

/**
 * Class ReportLetterTemplateTest Test
 */
class ReportLetterTemplateTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(GetList::class))
            ->once()
            ->andReturnUsing(
                function ($dto) {
                    $this->assertEquals(CategoryDataService::CATEGORY_REPORT, $dto->getCategory());
                    $this->assertEquals(CategoryDataService::DOC_SUB_CATEGORY_LETTER, $dto->getSubCategory());
                    return 'query';
                }
            )
            ->getMock();

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

        $sut = new ReportLetterTemplate();

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($expected, $sut->fetchListOptions());
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

        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->with(m::type(GetList::class))
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->getMock();

        $sut = new ReportLetterTemplate();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);
        $sut->fetchListOptions(null);
    }
}
