<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva\Traits;

use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Common\Controller\Lva\Traits\CrudActionTrait::class)]
final class CrudActionTraitTest extends MockeryTestCase
{
    public $mockFlashMessengerHelper;
    public const int ID = 9999;

    /** @var Stubs\CrudActionTraitStub | m\MockInterface */
    protected $sut;

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

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetCrudAction')]
    public function testGetCrudAction($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->callGetCrudAction($input));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetActionFromCrudAction')]
    public function testGetActionFromCrudAction($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->callGetActionFromCrudAction($input));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | string)> | null)>>
     *
     * @psalm-return list{list{array<never, never>, null}, list{list{array{foo: 'bar'}}, null}, list{list{array{action: 'bar'}}, array{action: 'bar'}}, list{list{array{action: 'bar'}, array{action: 'foo'}}, array{action: 'bar'}}, list{list{array{foo: 'bar'}, array{action: 'foo'}}, array{action: 'foo'}}}
     */
    public static function providerGetCrudAction(): \Iterator
    {
        yield [
            [],
            null
        ];
        yield [
            [
                [
                    'foo' => 'bar'
                ]
            ],
            null
        ];
        yield [
            [
                [
                    'action' => 'bar'
                ]
            ],
            ['action' => 'bar']
        ];
        yield [
            [
                [
                    'action' => 'bar'
                ],
                [
                    'action' => 'foo'
                ]
            ],
            ['action' => 'bar']
        ];
        yield [
            [
                [
                    'foo' => 'bar'
                ],
                [
                    'action' => 'foo'
                ]
            ],
            ['action' => 'foo']
        ];
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<int> | string)> | string)>>
     *
     * @psalm-return list{list{array{action: 'BAR'}, 'bar'}, list{array{action: array{BAR: 1}}, 'bar'}}
     */
    public static function providerGetActionFromCrudAction(): \Iterator
    {
        yield [
            ['action' => 'BAR'],
            'bar'
        ];
        yield [
            ['action' => ['BAR' => 1]],
            'bar'
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCrudAction')]
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

        $this->assertEquals('RESPONSE', $response);
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<string> | int)> | int | string)> | string | null)>>
     *
     * @psalm-return list{array{route: null, data: array{id: 9999, action: 'add'}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'add'}}, array{route: 'foo/bar', data: array{id: 9999, action: 'edit'}, childIdPrmName: 'some_other_id', baseRoute: null, expectRoute: 'foo/bar', expectRouteParams: array{action: 'edit', some_other_id: 9999}}, array{route: null, data: array{id: list{9999, 222}, action: 'edit'}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'edit', child_id: '9999,222'}}, array{route: null, data: array{action: array{edit: array{9999: 'foo'}}}, childIdPrmName: null, baseRoute: null, expectRoute: null, expectRouteParams: array{action: 'edit', child_id: 9999}}, array{route: null, data: array{action: 'add'}, childIdPrmName: null, baseRoute: 'unit_BaseRoute', expectRoute: 'unit_BaseRoute/action', expectRouteParams: array{action: 'add'}}}
     */
    public static function dpTestHandleCrudAction(): \Iterator
    {
        //  test WithIdWhenNotRequired
        yield [
            'route' => null,
            'data' => [
                'id' => self::ID,
                'action' => 'add'
            ],
            'childIdPrmName' => null,
            'baseRoute' => null,
            'expectRoute' => null,
            'expectRoutePrms' => ['action' => 'add'],
        ];
        //  test WithIdWhenIdRequiredWithCustomParams
        yield [
            'route' => 'foo/bar',
            'data' => [
                'id' => self::ID,
                'action' => 'edit',
            ],
            'childIdPrmName' => 'some_other_id',
            'baseRoute' => null,
            'expectRoute' => 'foo/bar',
            'expectRoutePrms' => [
                'action' => 'edit',
                'some_other_id' => self::ID,
            ],
        ];
        //  test WithMultipleIdsWhenIdRequired
        yield [
            'route' => null,
            'data' => [
                'id' => [self::ID, 222],
                'action' => 'edit',
            ],
            'childIdPrmName' => null,
            'baseRoute' => null,
            'expectRoute' => null,
            'expectRoutePrms' => [
                'action' => 'edit',
                'child_id' => self::ID . ',222',
            ],
        ];
        //  test WithIdWhenIdRequiredAlternativeDataFormat
        yield [
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
            'expectRoutePrms' => [
                'action' => 'edit',
                'child_id' => self::ID,
            ],
        ];
        //  test WithoutIdWhenNotRequired
        yield [
            'route' => null,
            'data' => [
                'action' => 'add'
            ],
            'childIdPrmName' => null,
            'baseRoute' => 'unit_BaseRoute',
            'expectRoute' => 'unit_BaseRoute/action',
            'expectRoutePrms' => [
                'action' => 'add',
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetBaseRoute')]
    public function testGetBaseRoute($baseRoute, $lva, $expect): void
    {
        $this->sut->baseRoute = $baseRoute;
        $this->sut->lva = $lva;

        $this->assertEquals($expect, $this->sut->callGetBaseRoute());
    }

    /**
     * @return \Iterator<(int | string), array<(string | null)>>
     *
     * @psalm-return list{array{baseRoute: null, lva: null, expect: null}, array{baseRoute: '', lva: null, expect: null}, array{baseRoute: 'unit base %s route', lva: 'unit_Lva', expect: 'unit base unit_Lva route'}, array{baseRoute: 'unit_BaseRoute', lva: null, expect: 'unit_BaseRoute'}}
     */
    public static function dpTestGetBaseRoute(): \Iterator
    {
        yield [
            'baseRoute' => null,
            'lva' => null,
            'expect' => null,
        ];
        yield [
            'baseRoute' => '',
            'lva' => null,
            'expect' => null,
        ];
        yield [
            'baseRoute' => 'unit base %s route',
            'lva' => 'unit_Lva',
            'expect' => 'unit base unit_Lva route',
        ];
        yield [
            'baseRoute' => 'unit_BaseRoute',
            'lva' => null,
            'expect' => 'unit_BaseRoute',
        ];
    }
}
