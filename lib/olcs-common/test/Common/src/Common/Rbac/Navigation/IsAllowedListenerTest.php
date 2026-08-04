<?php

declare(strict_types=1);

namespace CommonTest\Common\Rbac\Navigation;

use Common\Rbac\Navigation\IsAllowedListener;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Mvc\MvcEvent;
use Laminas\Navigation;
use LmcRbacMvc\Guard\GuardInterface;
use LmcRbacMvc\Options\ModuleOptions;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Rbac\Navigation\IsAllowedListener::class)]
final class IsAllowedListenerTest extends MockeryTestCase
{
    /** @var  m\MockInterface|ModuleOptions */
    private $mockModuleOptions;

    /** @var  m\MockInterface|AuthorizationService */
    private $mockAuthSrv;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockModuleOptions = m::mock(ModuleOptions::class);
        $this->mockAuthSrv = m::mock(AuthorizationService::class);
    }

    public function testAcceptNotMvcPage(): void
    {
        $mockPage = m::mock(Navigation\Page\AbstractPage::class);

        /** @var m\MockInterface|MvcEvent $mockEvent */
        $mockEvent = m::mock(MvcEvent::class)
            ->shouldReceive('getParam')
            ->once()
            ->with('page')
            ->andReturn($mockPage)
            ->getMock();

        $sut = new IsAllowedListener();
        $this->assertTrue($sut->accept($mockEvent));
    }

    public function testAcceptOk(): void
    {
        $mockPage = m::mock(Navigation\Page\Mvc::class)->makePartial();

        /** @var m\MockInterface|MvcEvent $mockEvent */
        $mockEvent = m::mock(MvcEvent::class)
            ->shouldReceive('getParam')
            ->once()
            ->with('page')
            ->andReturn($mockPage)
            //
            ->shouldReceive('stopPropagation')
            ->once()
            ->withNoArgs()
            ->getMock();

        $sut = new IsAllowedListener();
        $this->assertFalse($sut->accept($mockEvent));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestIsGranted')]
    public function testIsGranted($route, $rules, $policy, $isGranted, $expect): void
    {
        if ($isGranted !== null) {
            $this->mockAuthSrv
                ->shouldReceive('isGranted')
                ->once()
                ->with('unit_Permission')
                ->andReturn($isGranted);
        }

        $this->mockModuleOptions
            ->shouldReceive('getProtectionPolicy')
            ->once()
            ->andReturn($policy)
            //
            ->shouldReceive('getGuards')
            ->andReturn(
                [
                    \LmcRbacMvc\Guard\RoutePermissionsGuard::class => $rules,
                ]
            )
            ->getMock();

        /** @var Navigation\Page\Mvc $mockPage */
        $mockPage = m::mock(Navigation\Page\Mvc::class)->makePartial()
            ->shouldReceive('getRoute')
            ->once()
            ->andReturn($route)
            ->getMock();

        $sut = new IsAllowedListener()
            ->__invoke(
                $this->mockServiceLocator(),
                IsAllowedListener::class
            );

        $this->assertEquals($expect, $sut->isGranted($mockPage));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<string>> | bool | string | null)>>
     *
     * @psalm-return list{array{route: 'unit_Route', rules: array{unit_RouteOther: array<never, never>}, policy: 'deny', isGranted: null, expect: false}, array{route: 'unit_Route', rules: array{unit_Route: list{'*'}}, policy: null, isGranted: null, expect: true}, array{route: 'unit_Route', rules: array{unit_Route: array<never, never>}, policy: null, isGranted: null, expect: true}, array{route: 'unit_Route', rules: array{unit_Route: list{'unit_Permission'}}, policy: null, isGranted: false, expect: false}}
     */
    public static function dataProviderTestIsGranted(): \Iterator
    {
        //  rules not presented for route, policy DENY
        yield [
            'route' => 'unit_Route',
            'rules' => [
                'unit_RouteOther' => [],
            ],
            'policy' => GuardInterface::POLICY_DENY,
            'isGranted' => null,
            'expect' => false,
        ];
        //  rules presented with '*'
        yield [
            'route' => 'unit_Route',
            'rules' => [
                'unit_Route' => ['*'],
            ],
            'policy' => null,
            'isGranted' => null,
            'expect' => true,
        ];
        //  rules is empty
        yield [
            'route' => 'unit_Route',
            'rules' => [
                'unit_Route' => [],
            ],
            'policy' => null,
            'isGranted' => null,
            'expect' => true,
        ];
        //  rules has permission, but not Granted
        yield [
            'route' => 'unit_Route',
            'rules' => [
                'unit_Route' => ['unit_Permission'],
            ],
            'policy' => null,
            'isGranted' => false,
            'expect' => false,
        ];
    }

    /**
     * @return m\MockInterface|ContainerInterface
     */
    private function mockServiceLocator()
    {
        $closure = function ($class): \Mockery\MockInterface|\LmcRbacMvc\Options\ModuleOptions|\LmcRbacMvc\Service\AuthorizationService {
            $map = [
                AuthorizationService::class => $this->mockAuthSrv,
                ModuleOptions::class => $this->mockModuleOptions,
            ];

            return $map[$class];
        };

        return m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->andReturnUsing($closure)
            ->getMock();
    }
}
