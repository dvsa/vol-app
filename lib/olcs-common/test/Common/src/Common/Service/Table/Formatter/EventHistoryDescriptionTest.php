<?php

/**
 * Event history description formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\EventHistoryDescription;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Event history description formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EventHistoryDescriptionTest extends MockeryTestCase
{
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
        $this->sut = new EventHistoryDescription($this->router, $this->request, $this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     * @dataProvider provider
     */
    public function testFormat(
        $data,
        $expectedRouteName,
        $expectedUrlParams,
        $expectedUrl,
        $expectedOutput
    ): void {
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($expectedRouteName, $expectedUrlParams, [], true)
            ->andReturn($expectedUrl)
            ->getMock();

        $this->router
            ->shouldReceive('match')
            ->with($this->request)
            ->andReturn(
                m::mock()
                    ->shouldReceive('getMatchedRouteName')
                    ->once()
                    ->andReturn($expectedRouteName)
                    ->getMock()
            )
            ->once();

        $this->assertEquals($expectedOutput, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'application event history' => [
                [
                    'application' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'lva-application/processing/event-history',
                [
                    'action' => 'edit',
                    'application' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'variation event history' => [
                [
                    'application' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => ['description' => 'foo']
                ],
                'lva-application/processing/event-history',
                [
                    'action' => 'edit',
                    'application' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'licence event history' => [
                [
                    'licence' => ['id' => 2],
                    'id' => 1,
                ],
                'licence/processing/event-history',
                [
                    'action' => 'edit',
                    'licence' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar"></a>'
            ],
            'busreg event history' => [
                [
                    'busReg' => 2,
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'licence/bus-processing/event-history',
                [
                    'action' => 'edit',
                    'busRegId' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'transport manager event history' => [
                [
                    'transportManager' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'transport-manager/processing/event-history',
                [
                    'action' => 'edit',
                    'transportManager' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'operator event history' => [
                [
                    'organisation' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'operator/processing/history',
                [
                    'action' => 'edit',
                    'organisation' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'case event history' => [
                [
                    'case' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'processing_history',
                [
                    'action' => 'edit',
                    'case' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
            'irhp application event history' => [
                [
                    'irhpApplication' => ['id' => 2],
                    'id' => 1,
                    'eventHistoryType' => [
                        'description' => 'foo'
                    ]
                ],
                'processing_history',
                [
                    'action' => 'edit',
                    'irhpApplication' => 2,
                    'id' => 1,
                ],
                'bar',
                '<a class="govuk-link js-modal-ajax" href="bar">foo</a>'
            ],
        ];
    }

    /**
     * Test format with exception
     */
    public function testFormatWithException(): void
    {
        $this->expectException(\Exception::class);

        $this->router
            ->shouldReceive('match')
            ->with($this->request)
            ->andReturn(
                m::mock()
                    ->shouldReceive('getMatchedRouteName')
                    ->once()
                    ->andReturn('foo')
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals('foo', $this->sut->format([], []));
    }
}
