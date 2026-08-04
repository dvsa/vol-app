<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\RefData;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList as Qry;

/**
 * Class RefDataTest
 * @package CommonTest\Service
 */
final class RefDataTest extends RefDataTestCase
{
    /** @var RefData */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new RefData($this->refDataServices);
    }

    public function testFormatData(): void
    {
        $source = self::getSingleSource();
        $expected = self::getSingleExpected();

        $this->assertEquals($expected, $this->sut->formatData($source));
    }

    public function testFormatDataForGroups(): void
    {
        $source = self::getGroupSource();
        $expected = self::getGroupExpected();

        $this->assertEquals($expected, $this->sut->formatDataForGroups($source));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideFetchListOptions')]
    public function testFetchListOptions($source, $expected, $useGroups): void
    {
        $this->sut->setData('test', $source);

        $this->assertEquals($expected, $this->sut->fetchListOptions('test', $useGroups));
    }

    /**
     * @return \Iterator<(int | string), array<mixed>>
     *
     * @psalm-return list{list{false, array<never, never>, false}, list{array, array, false}, list{mixed, array, true}}
     */
    public static function provideFetchListOptions(): \Iterator
    {
        yield [false, [], false];
        yield [self::getSingleSource(), self::getSingleExpected(), false];
        yield [self::getGroupSource(), self::getGroupExpected(), true];
    }

    /**
     * @return array
     */
    protected static function getGroupExpected()
    {
        return [
            'parent' =>  [
                'label' => 'Parent',
                'options' =>  [],
            ],
            'p1' =>  [
                'options' =>  [
                  'val-1' => 'Value 1',
                ],
            ],
            'p2' =>  [
                'options' =>  [
                  'val-2' => 'Value 2',
                ],
            ],
            'p3' =>  [
                'options' =>  [
                  'val-3' => 'Value 3',
                ],
            ],
        ];
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
            ['id' => 'val-1', 'description' => 'Value 1'],
            ['id' => 'val-2', 'description' => 'Value 2'],
            ['id' => 'val-3', 'description' => 'Value 3'],
        ];
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{array{id: 'parent', description: 'Parent'}, array{id: 'val-1', description: 'Value 1', parent: array{id: 'p1', description: 'd1'}}, array{id: 'val-2', description: 'Value 2', parent: array{id: 'p2', description: 'd2'}}, array{id: 'val-3', description: 'Value 3', parent: array{id: 'p3', description: 'd3'}}}
     */
    protected static function getGroupSource(): array
    {
        return [
            ['id' => 'parent', 'description' => 'Parent'],
            ['id' => 'val-1', 'description' => 'Value 1', 'parent' => ['id' => 'p1', 'description' => 'd1']],
            ['id' => 'val-2', 'description' => 'Value 2', 'parent' => ['id' => 'p2', 'description' => 'd2']],
            ['id' => 'val-3', 'description' => 'Value 3', 'parent' => ['id' => 'p3', 'description' => 'd3']],
        ];
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];
        $params = [
            'refDataCategory' => 'cat',
            'language' => 'en'
        ];

        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['refDataCategory'], $dto->getRefDataCategory());
                    $this->assertEquals($params['language'], $dto->getLanguage());
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

        $this->languagePreferenceService->shouldReceive('getPreference')
            ->once()
            ->andReturn('en');

        $this->assertEquals($results['results'], $this->sut->fetchListData('cat'));
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

        $this->languagePreferenceService->shouldReceive('getPreference')
            ->once()
            ->andReturn('en');

        $this->sut->fetchListData('cat');
    }
}
