<?php

namespace CommonTest\Common\Controller\Lva\Traits;

use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 * @covers \Common\Controller\Lva\Traits\CrudActionTrait
 */
class CrudActionTraitTest extends MockeryTestCase
{
    public $mockFlashMessengerHelper;
    public const ID = 9999;

    /** @var Stubs\CrudActionTraitStub | m\MockInterface */
    protected $sut;

    /** @var \Laminas\ServiceManager\ServiceManager | m\MockInterface */
    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->sut = m::mock(Stubs\CrudActionTraitStub::class, [$this->mockFlashMessengerHelper])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider providerGetCrudAction
     */
    public function testGetCrudAction($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->callGetCrudAction($input));
    }

    /**
     * @dataProvider providerGetActionFromCrudAction
     */
    public function testGetActionFromCrudAction($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->callGetActionFromCrudAction($input));
    }

    /**
     * @return ((string|string[])[]|null)[][]
     *
     * @psalm-return list{list{array<never, never>, null}, list{list{array{foo: 'bar'}}, null}, list{list{array{action: 'bar'}}, array{action: 'bar'}}, list{list{array{action: 'bar'}, array{action: 'foo'}}, array{action: 'bar'}}, list{list{array{foo: 'bar'}, array{action: 'foo'}}, array{action: 'foo'}}}
     */
    public function providerGetCrudAction(): array
    {
        return [
            [
                [],
                null
            ],
            [
                [
                    [
                        'foo' => 'bar'
                    ]
                ],
                null
            ],
            [
                [
                    [
                        'action' => 'bar'
                    ]
                ],
                ['action' => 'bar']
            ],
            [
                [
                    [
                        'action' => 'bar'
                    ],
                    [
                        'action' => 'foo'
                    ]
                ],
                ['action' => 'bar']
            ],
            [
                [
                    [
                        'foo' => 'bar'
                    ],
                    [
                        'action' => 'foo'
                    ]
                ],
                ['action' => 'foo']
            ]
        ];
    }

    /**
     * @return ((int[]|string)[]|string)[][]
     *
     * @psalm-return list{list{array{action: 'BAR'}, 'bar'}, list{array{action: array{BAR: 1}}, 'bar'}}
     */
    public function providerGetActionFromCrudAction(): array
    {
        return [
            [
                ['action' => 'BAR'],
                'bar'
            ],
            [
                ['action' => ['BAR' => 1]],
                'bar'
            ]
        ];
    }

    /**
     * @dataProvider dpTestHandleCrudAction
     */
    public function testHandleCrudAction($route, $data, $childIdPrmName, $baseRoute, $expectRoute, $expectRoutePrms): void
    {
        $rowsNotRequired = ['add'];
        $childIdPrmName = $childIdPrmName ?: 'child_id';

        $this->sut
            ->shouldReceive('getBaseRoute')
            ->times(
                $route !== null
                ? 0
                : (
                    $baseRoute !== null
                    ? 2
                    : 1
                )
            )
            ->andReturn($baseRoute)
            //
            ->shouldReceive('redirect->toRoute')
            ->once()
            ->with($expectRoute, $expectRoutePrms, ['query' => null], true)
            ->andReturn('RESPONSE');

        $response = $this->sut->callHandleCrudAction($data, $rowsNotRequired, $childIdPrmName, $route);

        self::assertEquals('RESPONSE', $response);
    }

    /**
     * @return (((int|string[])[]|int|string)[]|null|string)[][]
     *
     * @psalm-return list{array{route: null, data: array{id: 9999, action: 'add'}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'add'}}, array{route: 'foo/bar', data: array{id: 9999, action: 'edit'}, childIdPrmName: 'some_other_id', baseRoute: null, expectRoute: 'foo/bar', expectRouteParams: array{action: 'edit', some_other_id: 9999}}, array{route: null, data: array{id: list{9999, 222}, action: 'edit'}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'edit', child_id: '9999,222'}}, array{route: null, data: array{action: array{edit: array{9999: 'foo'}}}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'edit', child_id: 9999}}, array{route: null, data: array{action: 'add'}, childIdPrmName: null, baseRoute: 'unit_BaseRoute', expectRoute: 'unit_BaseRoute/action', expectRouteParams: array{action: 'add'}}}
     */
    public function dpTestHandleCrudAction(): array
    {
        return [
            //  test WithIdWhenNotRequired
            [
                'route' => null,
                'data' => [
                    'id' => self::ID,
                    'action' => 'add'
                ],
                'childIdPrmName' => null,
                'baseRoute' => null,
                'expectRoute' => null,
                'expectRouteParams' => ['action' => 'add'],
            ],
            //  test WithIdWhenIdRequiredWithCustomParams
            [
                'route' => 'foo/bar',
                'data' => [
                    'id' => self::ID,
                    'action' => 'edit',
                ],
                'childIdPrmName' => 'some_other_id',
                'baseRoute' => null,
                'expectRoute' => 'foo/bar',
                'expectRouteParams' => [
                    'action' => 'edit',
                    'some_other_id' => self::ID,
                ],
            ],
            //  test WithMultipleIdsWhenIdRequired
            [
                'route' => null,
                'data' => [
                    'id' => [self::ID, 222],
                    'action' => 'edit',
                ],
                'childIdPrmName' => null,
                'baseRoute' => null,
                'expectRoute' => null,
                'expectRouteParams' => [
                    'action' => 'edit',
                    'child_id' => self::ID . ',222',
                ],
            ],
            //  test WithIdWhenIdRequiredAlternativeDataFormat
            [
                'route' => null,
                'data' => [
                    'action' => [
                        'edit' => [
                            self::ID => 'foo',
                        ],
                    ],
                ],
                'childIdPrmName' => null,
                'baseRoute' => null,
                'expectRoute' => null,
                'expectRouteParams' => [
                    'action' => 'edit',
                    'child_id' => self::ID,
                ],
            ],
            //  test WithoutIdWhenNotRequired
            [
                'route' => null,
                'data' => [
                    'action' => 'add'
                ],
                'childIdPrmName' => null,
                'baseRoute' => 'unit_BaseRoute',
                'expectRoute' => 'unit_BaseRoute/action',
                'expectRouteParams' => [
                    'action' => 'add',
                ],
            ],
        ];
    }

    public function testHandleCrudActionWithoutIdWhenIdRequired(): void
    {
        $data = [
            'action' => 'edit'
        ];
        $rowsNotRequired = ['add'];
        $childIdParamName = 'child_id';
        $route = null;

        $this->mockFlashMessengerHelper->shouldReceive('addWarningMessage')
            ->once()
            ->with('please-select-row');

        $this->sut->shouldReceive('redirect->refresh')
            ->once()
            ->andReturn('RESPONSE');

        $response = $this->sut->callHandleCrudAction($data, $rowsNotRequired, $childIdParamName, $route);

        $this->assertEquals('RESPONSE', $response);
    }

    /**
     * @dataProvider dpTestGetBaseRoute
     */
    public function testGetBaseRoute($baseRoute, $lva, $expect): void
    {
        $this->sut->baseRoute = $baseRoute;
        $this->sut->lva = $lva;

        static::assertEquals($expect, $this->sut->callGetBaseRoute());
    }

    /**
     * @return (null|string)[][]
     *
     * @psalm-return list{array{baseRoute: null, lva: null, expect: null}, array{baseRoute: '', lva: null, expect: null}, array{baseRoute: 'unit base %s route', lva: 'unit_Lva', expect: 'unit base unit_Lva route'}, array{baseRoute: 'unit_BaseRoute', lva: null, expect: 'unit_BaseRoute'}}
     */
    public function dpTestGetBaseRoute(): array
    {
        return [
            [
                'baseRoute' => null,
                'lva' => null,
                'expect' => null,
            ],
            [
                'baseRoute' => '',
                'lva' => null,
                'expect' => null,
            ],
            [
                'baseRoute' => 'unit base %s route',
                'lva' => 'unit_Lva',
                'expect' => 'unit base unit_Lva route',
            ],
            [
                'baseRoute' => 'unit_BaseRoute',
                'lva' => null,
                'expect' => 'unit_BaseRoute',
            ],
        ];
    }
}
