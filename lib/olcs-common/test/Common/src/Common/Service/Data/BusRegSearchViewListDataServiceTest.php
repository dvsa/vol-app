<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\BusRegSearchViewListDataService;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewContextList as Qry;
use Mockery as m;

/**
 * Class BusRegSearchViewListDataServiceTest
 * @package OlcsTest\Service\Data
 */
class BusRegSearchViewListDataServiceTest extends AbstractDataServiceTestCase
{
    /** @var BusRegSearchViewListDataService */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new BusRegSearchViewListDataService($this->abstractDataServiceServices);
    }

    /**
     * @dataProvider provideFetchListOptions
     * @param $context
     * @param $mockResultData
     * @param $expected
     */
    public function testFetchListOptions($context, $mockResultData, $expected): void
    {
        $this->sut->setData('BusRegSearchView' . ucfirst($context), $mockResultData);

        $this->assertEquals($expected, $this->sut->fetchListOptions($context));
    }

    public function testFetchListOptionsInvalidContext(): void
    {
        $this->expectException(DataServiceException::class);

        $context = 'invalid';

        $this->sut->fetchListOptions($context);
    }

    /**
     * @dataProvider provideFetchListData
     */
    public function testFetchListData($context, $expected): void
    {
        $params = [
            'context' => $context,
            'order' => 'ASC'
        ];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['context'], $dto->getContext());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn(['results' => $expected])
            ->once()
            ->getMock();

        $this->sut->setData('BusRegSearchView' . ucfirst($context), null);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListData($context));
    }

    /**
     * @return ((string|string[])[]|string)[][]
     *
     * @psalm-return list{list{'licence', list{array{licNo: 'UB1234', licId: '111'}, array{licNo: 'UB1235', licId: '222'}, array{licNo: 'UB1236', licId: '333'}}, array{111: 'UB1234', 222: 'UB1235', 333: 'UB1236'}}, list{'organisation', list{array{organisationName: 'ABC Ltd', organisationId: '111'}, array{organisationName: 'CDE Ltd', organisationId: '222'}, array{organisationName: 'FGH Ltd', organisationId: '333'}}, array{111: 'ABC Ltd', 222: 'CDE Ltd', 333: 'FGH Ltd'}}, list{'busRegStatus', list{array{busRegStatusDesc: 's1', busRegStatus: '111'}, array{busRegStatusDesc: 's2', busRegStatus: '222'}, array{busRegStatusDesc: 's3', busRegStatus: '333'}}, array{111: 's1', 222: 's2', 333: 's3'}}}
     */
    public function provideFetchListOptions(): array
    {
        return [
            [
                'licence',
                [
                    0 => ['licNo' => 'UB1234', 'licId' => '111'],
                    1 => ['licNo' => 'UB1235', 'licId' => '222'],
                    2 => ['licNo' => 'UB1236', 'licId' => '333']
                ],
                [
                    111 => 'UB1234',
                    222 => 'UB1235',
                    333 => 'UB1236'
                ]
            ],
            [
                'organisation',
                [
                    0 => ['organisationName' => 'ABC Ltd', 'organisationId' => '111'],
                    1 => ['organisationName' => 'CDE Ltd', 'organisationId' => '222'],
                    2 => ['organisationName' => 'FGH Ltd', 'organisationId' => '333']
                ],
                [
                    111 => 'ABC Ltd',
                    222 => 'CDE Ltd',
                    333 => 'FGH Ltd'
                ]
            ],

            [
                'busRegStatus',
                [
                    0 => ['busRegStatusDesc' => 's1', 'busRegStatus' => '111'],
                    1 => ['busRegStatusDesc' => 's2', 'busRegStatus' => '222'],
                    2 => ['busRegStatusDesc' => 's3', 'busRegStatus' => '333']
                ],
                [
                    111 => 's1',
                    222 => 's2',
                    333 => 's3'
                ]
            ],
        ];
    }

    /**
     * @return (string|string[][])[][]
     *
     * @psalm-return list{list{'licence', list{array{licNo: 'UB1234', licId: '111'}, array{licNo: 'UB1235', licId: '222'}, array{licNo: 'UB1236', licId: '333'}}}, list{'organisation', list{array{organisationName: 'ABC Ltd', organisationId: '111'}, array{organisationName: 'CDE Ltd', organisationId: '222'}, array{organisationName: 'FGH Ltd', organisationId: '333'}}}, list{'busRegStatus', list{array{busRegStatusDesc: 's1', busRegStatus: '111'}, array{busRegStatusDesc: 's2', busRegStatus: '222'}, array{busRegStatusDesc: 's3', busRegStatus: '333'}}}}
     */
    public function provideFetchListData(): array
    {
        return [
            [
                'licence',
                [
                    0 => ['licNo' => 'UB1234', 'licId' => '111'],
                    1 => ['licNo' => 'UB1235', 'licId' => '222'],
                    2 => ['licNo' => 'UB1236', 'licId' => '333']
                ]
            ],
            [
                'organisation',
                [
                    0 => ['organisationName' => 'ABC Ltd', 'organisationId' => '111'],
                    1 => ['organisationName' => 'CDE Ltd', 'organisationId' => '222'],
                    2 => ['organisationName' => 'FGH Ltd', 'organisationId' => '333']
                ]
            ],

            [
                'busRegStatus',
                [
                    0 => ['busRegStatusDesc' => 's1', 'busRegStatus' => '111'],
                    1 => ['busRegStatusDesc' => 's2', 'busRegStatus' => '222'],
                    2 => ['busRegStatusDesc' => 's3', 'busRegStatus' => '333']
                ]
            ]
        ];
    }
}
