<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Venue;
use Common\Service\Data\Licence as LicenceDataService;
use Mockery as m;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Venue\VenueList as Qry;

/**
 * Class Venue Test
 * @package CommonTest\Service
 */
final class VenueTest extends AbstractDataServiceTestCase
{
    /** @var Venue */
    private $sut;

    /** @var LicenceDataService */
    protected $licenceDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->licenceDataService = m::mock(LicenceDataService::class);
        $this->sut = new Venue(
            $this->abstractDataServiceServices,
            $this->licenceDataService
        );
    }

    public function testFormatData(): void
    {
        $source = self::getSingleSource();
        $expected = self::getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($input, $expected): void
    {
        $this->licenceDataService->shouldReceive('fetchLicenceData')
            ->once()
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag' => true,
                    'goodsOrPsv' => ['id' => 'lcat_gv'],
                    'trafficArea' => ['id' => 'B']
                ]
            );

        $this->sut->setData('Venue', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    /**
     * @return \Iterator<(int | string), array<(array<mixed> | false)>>
     *
     * @psalm-return list{list{array, array}, list{false, array<never, never>}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [self::getSingleSource(), self::getSingleExpected()];
        yield [false, []];
    }

    /**
     * @param $input
     * @param $expectedTrafficArea
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListData')]
    public function testFetchListData($input, $expectedTrafficArea): void
    {
        $results = ['results' => 'results'];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($expectedTrafficArea) {
                    $this->assertEquals($expectedTrafficArea, $dto->getTrafficArea());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData($input));
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string | null)>>
     *
     * @psalm-return list{list{array{trafficArea: 'B'}, 'B'}, list{array<never, never>, null}}
     */
    public static function provideFetchListData(): \Iterator
    {
        yield [['trafficArea' => 'B'], 'B'];
        yield [[], null];
    }

    public function testFetchLicenceDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

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

        $this->sut->fetchListData(['trafficArea' => 'B']);
    }

    /**
     * @return array
     */
    protected static function getSingleExpected()
    {
        return [
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleSource()
    {
        return [
            ['id' => 'val-1', 'name' => 'Value 1'],
            ['id' => 'val-2', 'name' => 'Value 2'],
            ['id' => 'val-3', 'name' => 'Value 3'],
        ];
    }
}
