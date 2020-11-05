<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Common\Service\Data\CategoryDataService;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Olcs\Service\Data\ReportEmailTemplate;
use Mockery as m;

/**
 * Class ReportEmailTemplateTest Test
 */
class ReportEmailTemplateTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $mockTransferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class)
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function (AvailableTemplates $dto) {
                    $this->assertEquals(CategoryDataService::CATEGORY_REPORT, $dto->getEmailTemplateCategory());
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

        $sut = new ReportEmailTemplate();

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
                            'id' => 'AB',
                            'name' => 'sometemplate',
                        ],
                        [
                            'id' => 'CD',
                            'name' => 'someothertemplate',
                        ]
                    ]
                ],
                'expected' => [
                    'sometemplate' => 'sometemplate',
                    'someothertemplate' => 'someothertemplate',
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
            ->with(m::type(AvailableTemplates::class))
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock(Response::class)
            ->shouldReceive('isOk')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->getMock();

        $sut = new ReportEmailTemplate();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);
        $sut->fetchListOptions(null);
    }
}
