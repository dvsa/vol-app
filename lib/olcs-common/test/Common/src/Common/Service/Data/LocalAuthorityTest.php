<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\LocalAuthority;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\LocalAuthority\LocalAuthorityList as Qry;

/**
 * Class LocalAuthority Test
 * @package CommonTest\Service
 */
final class LocalAuthorityTest extends AbstractDataServiceTestCase
{
    /** @var LocalAuthority */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new LocalAuthority($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $source = self::getSingleSource();
        $expected = self::getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    public function testFormatDataForGroups(): void
    {
        $source = self::getSingleSource();
        $expected = self::getGroupsExpected();

        $this->assertEquals($expected, $this->sut->formatDataForGroups($source));
    }

    /**
     * @param $input
     * @param $expected
     * @param $useGroups
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($input, $expected, $useGroups): void
    {
        $this->sut->setData('LocalAuthority', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions('', $useGroups));
    }

    /**
     * @return \Iterator<(int | string), array<(array<mixed> | bool)>>
     *
     * @psalm-return list{list{array, array, false}, list{false, array<never, never>, false}, list{array, array, true}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [self::getSingleSource(), self::getSingleExpected(), false];
        yield [false, [], false];
        yield [self::getSingleSource(), self::getGroupsExpected(), true];
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];

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

        $this->assertEquals($results['results'], $this->sut->fetchListData());
        $this->assertEquals($results['results'], $this->sut->fetchListData()); //ensure data is cached
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

        $this->sut->fetchListData();
    }

    /**
     * @return (array|false)[][]
     *
     * @psalm-return list{list{false, false}, list{array{Results: array}, array}, list{array{some: 'data'}, false}}
     */
    public function provideFetchListData(): array
    {
        return [
            [false, false],
            [['Results' => self::getSingleSource()], self::getSingleSource()],
            [['some' => 'data'],  false]
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleExpected()
    {
        return [
            '1' => 'A1 Council',
            '2' => 'B Council',
            '3' => 'C Council',
            '4' => 'A2 Council',
        ];
    }

    /**
     * @return array
     */
    protected static function getGroupsExpected()
    {
        return [
            'A' => [
                'label' => 'AAA',
                'options' => [
                    '1' => 'A1 Council',
                    '4' => 'A2 Council',
                ],
            ],
            'B' => [
                'label' => 'BBB',
                'options' => [
                    '2' => 'B Council',
                ],
            ],
            'C' => [
                'label' => 'CCC',
                'options' => [
                    '3' => 'C Council',
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    protected static function getSingleSource()
    {
        return [
            ['id' => '1', 'description' => 'A1 Council', 'trafficArea' => ['name' => 'AAA', 'id' => 'A']],
            ['id' => '2', 'description' => 'B Council', 'trafficArea' => ['name' => 'BBB', 'id' => 'B']],
            ['id' => '3', 'description' => 'C Council', 'trafficArea' => ['name' => 'CCC', 'id' => 'C']],
            ['id' => '4', 'description' => 'A2 Council', 'trafficArea' => ['name' => 'AAA', 'id' => 'A']],
        ];
    }
}
