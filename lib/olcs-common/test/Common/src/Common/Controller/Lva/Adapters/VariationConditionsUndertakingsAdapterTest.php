<?php

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter;
use Common\Service\Script\ScriptFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class VariationConditionsUndertakingsAdapterTest extends MockeryTestCase
{
    protected $sut;

    protected $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        $this->sut = new VariationConditionsUndertakingsAdapter($this->container);
    }

    public function testGetTableName(): void
    {
        $this->assertEquals('lva-variation-conditions-undertakings', $this->sut->getTableName());
    }

    public function testAttachMainScripts(): void
    {
        $mockScript = m::mock();
        $this->container->expects('get')->with(ScriptFactory::class)->andReturn($mockScript);

        $mockScript->shouldReceive('loadFile')
            ->with('lva-crud-delta');

        $this->sut->attachMainScripts();
    }

    /**
     * @return (int|null|string|string[][])[][][][]
     *
     * @psalm-return list{list{array<never, never>, array<never, never>}, list{list{array{id: 12, action: 'A'}, array{id: 13, action: null, variationRecords: array<never, never>}, array{id: 14, action: null, variationRecords: list{array{action: 'D'}}}}, list{array{id: 12, action: 'A'}, array{id: 13, action: 'E', variationRecords: array<never, never>}}}}
     */
    public function providerGetTableDataEmpty(): array
    {
        return [
            [
                [],
                []
            ],
            [
                [
                    [
                        'id' => 12,
                        'action' => 'A'
                    ],
                    [
                        'id' => 13,
                        'action' => null,
                        'variationRecords' => []
                    ],
                    [
                        'id' => 14,
                        'action' => null,
                        'variationRecords' => [
                            ['action' => 'D']
                        ]
                    ]
                ],
                [
                    [
                        'id' => 12,
                        'action' => 'A'
                    ],
                    [
                        'id' => 13,
                        'action' => 'E',
                        'variationRecords' => []
                    ]
                ],
            ]
        ];
    }

    /**
     * @return ((null|string|string[][])[]|string)[][]
     *
     * @psalm-return array{Added: list{array{action: 'A'}, 'A'}, Updated: list{array{action: 'U'}, 'U'}, Existing: list{array{action: null, variationRecords: array<never, never>}, 'E'}, Removed: list{array{variationRecords: list{array{action: 'D'}}}, 'R'}, Current: list{array{variationRecords: list{array{action: 'U'}}}, 'C'}}
     */
    public function providerDetermineAction(): array
    {
        return [
            'Added' => [
                ['action' => 'A'],
                'A'
            ],
            'Updated' => [
                ['action' => 'U'],
                'U'
            ],
            'Existing' => [
                ['action' => null, 'variationRecords' => []],
                'E'
            ],
            'Removed' => [
                ['variationRecords' => [['action' => 'D']]],
                'R'
            ],
            'Current' => [
                ['variationRecords' => [['action' => 'U']]],
                'C'
            ]
        ];
    }

    /**
     * @return ((null|string|string[][])[]|bool)[][]
     *
     * @psalm-return array{Added: list{array{action: 'A'}, true}, Updated: list{array{action: 'U'}, true}, Existing: list{array{action: null, variationRecords: array<never, never>}, true}, Removed: list{array{variationRecords: list{array{action: 'D'}}}, false}, Current: list{array{variationRecords: list{array{action: 'U'}}}, false}}
     */
    public function providerCanEditRecord(): array
    {
        return [
            'Added' => [
                ['action' => 'A'],
                true
            ],
            'Updated' => [
                ['action' => 'U'],
                true
            ],
            'Existing' => [
                ['action' => null, 'variationRecords' => []],
                true
            ],
            'Removed' => [
                ['variationRecords' => [['action' => 'D']]],
                false
            ],
            'Current' => [
                ['variationRecords' => [['action' => 'U']]],
                false
            ]
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'A'}, list{'U'}}
     */
    public function providerSaveEdit(): array
    {
        return [
            ['A'],
            ['U']
        ];
    }
}
