<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryReason;
use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as Qry;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

class PublicInquiryReasonTest extends AbstractPublicInquiryDataTestCase
{
    private $reasons = [
        ['id' => 12, 'sectionCode' => 'Section A', 'description' => 'Description 1'],
        ['id' => 15, 'sectionCode' => 'Section C', 'description' => 'Description 2'],
    ];

    /** @var PublicInquiryReason */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PublicInquiryReason($this->abstractPublicInquiryDataServices);
    }

    public function testFetchListData(): void
    {
        $results = ['results' => $this->reasons];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results, $this->sut->fetchListData([]));
    }

    public function testFetchPublicInquiryReasonDataFailure(): void
    {
        $results = [];
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEmpty($this->sut->fetchListData([]));
    }

    #[DataProvider('provideFetchListOptions')]
    public function testFetchListOptions(string $niFlag, string $goodsOrPsv): void
    {
        $this->licenceDataService->shouldReceive('getId')
            ->once()
            ->andReturn(7)
            ->shouldReceive('fetchLicenceData')
            ->withNoArgs()
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag' => $niFlag,
                    'goodsOrPsv' => ['id' => $goodsOrPsv],
                    'trafficArea' => ['id' => 'B']
                ]
            )
            ->once();

        $results = ['results' => self::SINGLE_SOURCE];
        $params = [
            'sort' => 'sectionCode, description',
            'order' => 'ASC, ASC',
            'niFlag' => $niFlag,
            'goodsOrPsv' => $goodsOrPsv,
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
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

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals(self::SINGLE_EXPECTED, $this->sut->fetchListOptions($params));
    }

    public static function provideFetchListOptions(): array
    {
        return [
            ['Y', 'lcat_psv'],
            ['N', 'lcat_psv'],
            ['Y', 'lcat_gv'],
            ['N', 'lcat_gv']
        ];
    }
}
