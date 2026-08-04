<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use CommonTest\Common\Service\Data\Stub\AbstractListDataServiceStub;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Data\AbstractListDataService::class)]
final class AbstractListDataServiceTest extends AbstractListDataServiceTestCase
{
    /** @var  AbstractListDataServiceStub */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new AbstractListDataServiceStub($this->abstractListDataServiceServices);
    }

    public function testFormatDataForGroup(): void
    {
        $data = [
            [
                'parent' => [
                    'id' => 9001,
                ],
                'id' => 7001,
                'description' => 'unit_Desc7001',
            ],
            [
                'id' => 9001,
                'description' => 'unit_WithChilds',
            ],
            [
                'id' => 8001,
                'description' => 'unit_Desc',
            ],
            [
                'parent' => [
                    'id' => 9001,
                ],
                'id' => 7003,
                'description' => 'unit_Desc7003',
            ],
        ];

        $actual = $this->sut->formatDataForGroups($data);

        $this->assertEquals([
            8001 => [
                'label' => 'unit_Desc',
                'options' => [],
            ],
            9001 => [
                'label' => 'unit_WithChilds',
                'options' => [
                    7001 => 'unit_Desc7001',
                    7003 => 'unit_Desc7003',
                ],
            ],
        ], $actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFetchListOptions')]
    public function testFetchListOptions($data, $useGroup, $expect): void
    {
        $context = 'unit_Context';

        $this->sut->mockFetchListData = $data;

        $actual = $this->sut->fetchListOptions($context, $useGroup);

        $this->assertEquals($expect, $actual);
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<(int | string)> | int | string)> | string)> | bool | null)>>
     *
     * @psalm-return list{array{data: null, useGroup: false, expect: array<never, never>}, array{data: list{array{id: 'unit_Id', description: 'unit_Desc'}}, useGroup: false, expect: array{unit_Id: 'unit_Desc'}}, array{data: list{array{parent: array{id: 9001}, id: 7001, description: 'unit_Desc7001'}, array{id: 9001, description: 'unit_WithChilds'}}, useGroup: true, expect: array{9001: array{label: 'unit_WithChilds', options: array{7001: 'unit_Desc7001'}}}}}
     */
    public static function dpTestFetchListOptions(): \Iterator
    {
        yield [
            'data' => null,
            'useGroup' => false,
            'expect' => [],
        ];
        yield [
            'data' => [
                [
                    'id' => 'unit_Id',
                    'description' => 'unit_Desc',
                ],
            ],
            'useGroup' => false,
            'expect' => [
                'unit_Id' => 'unit_Desc',
            ],
        ];
        yield [
            'data' => [
                [
                    'parent' => [
                        'id' => 9001,
                    ],
                    'id' => 7001,
                    'description' => 'unit_Desc7001',
                ],
                [
                    'id' => 9001,
                    'description' => 'unit_WithChilds',
                ],
            ],
            'useGroup' => true,
            'expect' => [
                9001 => [
                    'label' => 'unit_WithChilds',
                    'options' => [
                        7001 => 'unit_Desc7001',
                    ],
                ],
            ]
        ];
    }
}
