<?php

declare(strict_types=1);

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\EventManager\Event;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\Cases;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class CasesTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Cases();

        parent::setUp();
    }

    public function setupMockCase(mixed $id, mixed $data): void
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResult->shouldReceive('getResult')->with()->once()->andReturn($data);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
    }

    public function testAttach(): void
    {
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->expects('attach')
            ->with(
                RouteParams::EVENT_PARAM . 'case',
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onCase';
                }),
                1
            );

        $this->sut->attach($mockEventManager);
    }

    public function testOnCase(): void
    {
        $id = 69;
        $case = [
            'id' => $id,
            'application' => [
                'id' => 100,
            ],
            'licence' => [
                'id' => 101,
            ],
            'transportManager' => [
                'id' => 102,
            ],
            'tmDecisions' => [
                ['id' => 200]
            ],
            'latestNote' => [
                'comment' => 'latest note'
            ]
        ];

        $routeParam = new RouteParam();
        $routeParam->setValue($id);
        $routeParam->setTarget(
            m::mock()
                ->shouldReceive('trigger')->once()->with('application', 100)
                ->shouldReceive('trigger')->once()->with('licence', 101)
                ->shouldReceive('trigger')->once()->with('transportManager', 102)
                ->getMock()
        );

        $event = new Event(null, $routeParam);

        $this->setupMockCase($id, $case);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()->with('case')->andReturn(
                m::mock()->shouldReceive('set')->once()->with($case)->getMock()
            )
            ->shouldReceive('getContainer')->once()->with('note')->andReturn(
                m::mock()->shouldReceive('set')->once()->with($case['latestNote']['comment'])->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock(HelperPluginManager::class)
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigation = m::mock()
            ->shouldReceive('findOneById')->once()->with('case_opposition')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_legacy_offence')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_annual_test_history')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_prohibitions')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_statements')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_conditions_undertakings')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_impounding')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_processing_in_office_revocation')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->getMock();

        $this->sut->setNavigationService($mockNavigation);

        $this->sut->onCase($event);
    }

    public function testInvoke(): void
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockNavigation = m::mock();
        $mockQueryService = m::mock();
        $mockAnnotationBuilder = m::mock(AnnotationBuilder::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with(AnnotationBuilder::class)->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);

        $service = $this->sut->__invoke($mockSl, Cases::class);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockNavigation, $this->sut->getNavigationService());
        $this->assertSame($mockAnnotationBuilder, $this->sut->getAnnotationBuilder());
    }

    public function testOnCaseNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $id = 69;

        $routeParam = new RouteParam();
        $routeParam->setValue(69);

        $event = new Event(null, $routeParam);

        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);

        $this->sut->onCase($event);
    }
}
