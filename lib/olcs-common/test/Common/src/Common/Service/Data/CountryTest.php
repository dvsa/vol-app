<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Country;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountryList as Qry;

/**
 * Class Country Test
 * @package CommonTest\Service
 */
class CountryTest extends AbstractDataServiceTestCase
{
    /** @var Country */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Country($this->abstractDataServiceServices);
    }

    public function testFormatData(): void
    {
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     * @param $input
     * @param $expected
     */
    public function testFetchListOptions($input, $category, $expected): void
    {
        $this->sut->setData('Country', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions($category));
    }

    /**
     * @return (array|false|string)[][]
     *
     * @psalm-return list{list{array, '', array}, list{false, '', array<never, never>}, list{array, 'isMemberState', array{'val-1': 'Value 1', 'val-2': 'Value 2', 'val-3': 'Value 3'}}, list{array, 'ecmtConstraint', array{'val-2': 'Value 2', 'val-5': 'Value 5'}}, list{array, 'isPermitState', array{'val-3': 'Value 3', 'val-6': 'Value 6'}}}
     */
    public function provideFetchListOptions(): array
    {
        return [
            [$this->getSingleSource(), '', $this->getSingleExpected()],
            [false, '', []],
            [
                $this->getSingleSource(),
                'isMemberState',
                [
                    'val-1' => 'Value 1',
                    'val-2' => 'Value 2',
                    'val-3' => 'Value 3',
                ],
            ],
            [
                $this->getSingleSource(),
                'ecmtConstraint',
                [
                    'val-2' => 'Value 2',
                    'val-5' => 'Value 5',
                ],
            ],
            [
                $this->getSingleSource(),
                'isPermitState',
                [
                    'val-3' => 'Value 3',
                    'val-6' => 'Value 6',
                ],
            ],
        ];
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => 'countryDesc',
            'order' => 'ASC',
        ];
        $dto = Qry::create($params);

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
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData());
    }

    public function testFetchListDataWithException(): void
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
     * @return array
     */
    protected function getSingleExpected()
    {
        return [
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
            'val-4' => 'Value 4',
            'val-5' => 'Value 5',
            'val-6' => 'Value 6',
        ];
    }

    /**
     * @return array
     */
    protected function getSingleSource()
    {
        return [
            [
                'id' => 'val-1',
                'countryDesc' => 'Value 1',
                'isMemberState' => 'Y',
                'constraints' => [],
                'isPermitState' => false,
            ],
            [
                'id' => 'val-2',
                'countryDesc' => 'Value 2',
                'isMemberState' => 'Y',
                'constraints' => ['A'],
                'isPermitState' => false,
            ],
            [
                'id' => 'val-3',
                'countryDesc' => 'Value 3',
                'isMemberState' => 'Y',
                'constraints' => [],
                'isPermitState' => true,
            ],
            [
                'id' => 'val-4',
                'countryDesc' => 'Value 4',
                'isMemberState' => 'N',
                'constraints' => [],
                'isPermitState' => false,
            ],
            [
                'id' => 'val-5',
                'countryDesc' => 'Value 5',
                'isMemberState' => 'N',
                'constraints' => ['A'],
                'isPermitState' => false,
            ],
            [
                'id' => 'val-6',
                'countryDesc' => 'Value 6',
                'isMemberState' => 'N',
                'constraints' => [],
                'isPermitState' => true,
            ],
        ];
    }
}
