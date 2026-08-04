<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\BusRegBrowseListDataService;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseContextList as Qry;
use Mockery as m;

/**
 * Class BusRegBrowseListDataServiceTest
 * @package OlcsTest\Service\Data
 */
final class BusRegBrowseListDataServiceTest extends AbstractDataServiceTestCase
{
    private BusRegBrowseListDataService $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new BusRegBrowseListDataService($this->abstractDataServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($context, $result, $expected): void
    {
        $this->sut->setData('BusRegBrowse' . ucfirst((string) $context), $result);

        $this->assertEquals($expected, $this->sut->fetchListOptions($context));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | string)> | string | false)>>
     *
     * @psalm-return list{list{'eventRegistrationStatus', false, array<never, never>}, list{'eventRegistrationStatus', list{array{eventRegistrationStatus: 'A'}, array{eventRegistrationStatus: 'B'}, array{eventRegistrationStatus: 'C'}}, array{A: 'A', B: 'B', C: 'C'}}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [
            'eventRegistrationStatus',
            false,
            []
        ];
        yield [
            'eventRegistrationStatus',
            [
                ['eventRegistrationStatus' => 'A'],
                ['eventRegistrationStatus' => 'B'],
                ['eventRegistrationStatus' => 'C'],
            ],
            [
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListData')]
    public function testFetchListData($context, $result, $expected): void
    {
        $params = [
            'context' => $context,
            'sort' => $context,
            'order' => 'ASC'
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['context'], $dto->getContext());
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
            ->andReturn(['result' => $result])
            ->once()
            ->getMock();

        $this->sut->setData('BusRegBrowse' . ucfirst((string) $context), null);

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchListData($context));
        // test caching as well
        $this->assertEquals($expected, $this->sut->fetchListData($context));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<string>> | string)>>
     *
     * @psalm-return list{list{'eventRegistrationStatus', list{array{eventRegistrationStatus: 'A'}, array{eventRegistrationStatus: 'B'}, array{eventRegistrationStatus: 'C'}}, list{array{eventRegistrationStatus: 'A'}, array{eventRegistrationStatus: 'B'}, array{eventRegistrationStatus: 'C'}}}}
     */
    public static function provideFetchListData(): \Iterator
    {
        yield [
            'eventRegistrationStatus',
            [
                ['eventRegistrationStatus' => 'A'],
                ['eventRegistrationStatus' => 'B'],
                ['eventRegistrationStatus' => 'C'],
            ],
            [
                ['eventRegistrationStatus' => 'A'],
                ['eventRegistrationStatus' => 'B'],
                ['eventRegistrationStatus' => 'C'],
            ]
        ];
    }

    public function testFetchListDataThrowsException(): void
    {
        $this->expectException(DataServiceException::class);

        $context = 'eventRegistrationStatus';

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

        $this->sut->fetchListData($context);
    }
}
