<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TaskDescription;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Task description formatter tests
 */
class TaskDescriptionTest extends MockeryTestCase
{
    public $mockRouteMatch;
    protected $urlHelper;

    protected $router;

    protected $request;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->router = m::mock(TreeRouteStack::class);
        $this->request = m::mock(Request::class);
        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $this->sut = new TaskDescription($this->router, $this->request, $this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider dpTestFormat
     */
    public function testFormat($matchedRouteName, $params, $expected): void
    {
        $data = [
            'id' => 100,
            'description' => 'DESC',
        ];
        $query = ['q1' => 1];

        $this->router->shouldReceive('match')
            ->with($this->request)
            ->andReturn($this->mockRouteMatch);
        $this->mockRouteMatch->shouldReceive('getMatchedRouteName')
            ->withNoArgs()
            ->andReturn($matchedRouteName);

        $this->mockRouteMatch->shouldReceive('getParams')
            ->withNoArgs()
            ->andReturn($params);

        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'task_action',
                $expected,
                ['query' => $query]
            )
            ->andReturn('URL');

        $this->request->shouldReceive('getQuery->toArray')
            ->withNoArgs()
            ->andReturn($query);

        $this->assertEquals('<a href="URL" class="govuk-link js-modal-ajax">DESC</a>', $this->sut->format($data, []));
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{'unmatched-route', array<never, never>, array{task: 100, action: 'edit'}}, list{'licence/processing/tasks', array{licence: 201}, array{task: 100, action: 'edit', type: 'licence', typeId: 201}}, list{'lva-application/processing/tasks', array{application: 201}, array{task: 100, action: 'edit', type: 'application', typeId: 201}}, list{'transport-manager/processing/tasks', array{transportManager: 201}, array{task: 100, action: 'edit', type: 'tm', typeId: 201}}, list{'licence/bus-processing/tasks', array{busRegId: 201, licence: 202}, array{task: 100, action: 'edit', type: 'busreg', typeId: 201, licence: 202}}, list{'licence/irhp-application-processing/tasks', array{irhpAppId: 201, licence: 202}, array{task: 100, action: 'edit', type: 'irhpapplication', typeId: 201, licence: 202}}, list{'case_processing_tasks', array{case: 201}, array{task: 100, action: 'edit', type: 'case', typeId: 201}}, list{'operator/processing/tasks', array{organisation: 201}, array{task: 100, action: 'edit', type: 'organisation', typeId: 201}}}
     */
    public function dpTestFormat(): array
    {
        return [
            [
                'unmatched-route',
                [],
                [
                    'task' => 100,
                    'action' => 'edit'
                ],
            ],
            [
                'licence/processing/tasks',
                [
                    'licence' => 201,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'licence',
                    'typeId' => 201,
                ],
            ],
            [
                'lva-application/processing/tasks',
                [
                    'application' => 201,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'application',
                    'typeId' => 201,
                ],
            ],
            [
                'transport-manager/processing/tasks',
                [
                    'transportManager' => 201,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'tm',
                    'typeId' => 201,
                ],
            ],
            [
                'licence/bus-processing/tasks',
                [
                    'busRegId' => 201,
                    'licence' => 202,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'busreg',
                    'typeId' => 201,
                    'licence' => 202,
                ],
            ],
            [
                'licence/irhp-application-processing/tasks',
                [
                    'irhpAppId' => 201,
                    'licence' => 202,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'irhpapplication',
                    'typeId' => 201,
                    'licence' => 202,
                ],
            ],
            [
                'case_processing_tasks',
                [
                    'case' => 201,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'case',
                    'typeId' => 201,
                ],
            ],
            [
                'operator/processing/tasks',
                [
                    'organisation' => 201,
                ],
                [
                    'task' => 100,
                    'action' => 'edit',
                    'type' => 'organisation',
                    'typeId' => 201,
                ],
            ],
        ];
    }
}
