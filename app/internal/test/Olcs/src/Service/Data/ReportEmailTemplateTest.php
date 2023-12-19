<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Common\Service\Data\CategoryDataService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates;
use Olcs\Service\Data\ReportEmailTemplate;
use Mockery as m;

/**
 * Class ReportEmailTemplateTest Test
 */
class ReportEmailTemplateTest extends AbstractDataServiceTestCase
{
    /** @var ReportEmailTemplate */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ReportEmailTemplate($this->abstractDataServiceServices);
    }

    /**
     * @dataProvider dpTestFetchListOptions
     */
    public function testFetchListOptions($results, $expected)
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function (AvailableTemplates $dto) {
                    $this->assertEquals(CategoryDataService::CATEGORY_REPORT, $dto->getEmailTemplateCategory());
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

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(AvailableTemplates::class))
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
