<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\CommunityLicenceStatus;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Community licence status formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CommunityLicenceStatusTest extends MockeryTestCase
{
    public $sut;
    protected $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $router = m::mock(TreeRouteStack::class);
        $request = m::mock(Request::class);
        $this->sut = new CommunityLicenceStatus($this->urlHelper, $router, $request);

        $mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class)
            ->shouldReceive('getMatchedRouteName')
            ->andReturn('route')
            ->getMock();

        $request->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('toArray')
                    ->once()
                    ->andReturn(['foo' => 'bar'])
                    ->getMock()
            )
            ->once();

        $router
            ->shouldReceive('match')
            ->with($request)
            ->andReturn($mockRouteMatch)
            ->getMock();
    }

    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testFormat($data, $url): void
    {
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with('route', ['child_id' => $data['id'], 'action' => 'edit'], ['query' => ['foo' => 'bar']], true)
            ->andReturn('the_url')
            ->getMock();

        $this->assertEquals(
            $url,
            $this->sut->format($data, [])
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | int | string | null)> | string)>>
     *
     * @psalm-return list{list{array{id: 1, futureSuspension: array{startDate: '2017-01-01', endDate: '2018-01-01'}, currentSuspension: null, currentWithdrawal: null}, '<a class="govuk-link" href="the_url">Suspension due: 01/01/2017 to 01/01/2018</a>'}, list{array{id: 1, futureSuspension: array{startDate: '2017-01-01'}, currentSuspension: null, currentWithdrawal: null}, '<a class="govuk-link" href="the_url">Suspension due: 01/01/2017</a>'}, list{array{id: 1, futureSuspension: null, currentSuspension: array{startDate: '2016-01-01', endDate: '2018-01-01'}, currentWithdrawal: null}, '<a class="govuk-link" href="the_url">Suspended: 01/01/2016 to 01/01/2018</a>'}, list{array{id: 1, futureSuspension: null, currentSuspension: array{startDate: '2016-01-01'}, currentWithdrawal: null}, '<a class="govuk-link" href="the_url">Suspended: 01/01/2016</a>'}, list{array{id: 1, futureSuspension: null, currentSuspension: null, currentWithdrawal: array{startDate: '2016-01-01'}}, 'Withdrawn: 01/01/2016'}, list{array{id: 1, status: array{description: 'Expired'}, futureSuspension: null, currentSuspension: null, currentWithdrawal: null, expiredDate: '2016-01-01'}, 'Expired: 01/01/2016'}, list{array{id: 1, status: array{description: 'Pending'}, futureSuspension: null, currentSuspension: null, currentWithdrawal: null}, 'Pending'}}
     */
    public static function dataProvider(): \Iterator
    {
        yield [
            [
                'id' => 1,
                'futureSuspension' => [
                    'startDate' => '2017-01-01',
                    'endDate' => '2018-01-01'
                ],
                'currentSuspension' => null,
                'currentWithdrawal' => null
            ],
            '<a class="govuk-link" href="the_url">Suspension due: 01/01/2017 to 01/01/2018</a>'
        ];
        yield [
            [
                'id' => 1,
                'futureSuspension' => [
                    'startDate' => '2017-01-01'
                ],
                'currentSuspension' => null,
                'currentWithdrawal' => null
            ],
            '<a class="govuk-link" href="the_url">Suspension due: 01/01/2017</a>'
        ];
        yield [
            [
                'id' => 1,
                'futureSuspension' => null,
                'currentSuspension' => [
                    'startDate' => '2016-01-01',
                    'endDate' => '2018-01-01'
                ],
                'currentWithdrawal' => null
            ],
            '<a class="govuk-link" href="the_url">Suspended: 01/01/2016 to 01/01/2018</a>'
        ];
        yield [
            [
                'id' => 1,
                'futureSuspension' => null,
                'currentSuspension' => [
                    'startDate' => '2016-01-01'
                ],
                'currentWithdrawal' => null
            ],
            '<a class="govuk-link" href="the_url">Suspended: 01/01/2016</a>'
        ];
        yield [
            [
                'id' => 1,
                'futureSuspension' => null,
                'currentSuspension' => null,
                'currentWithdrawal' => [
                    'startDate' => '2016-01-01'
                ]
            ],
            'Withdrawn: 01/01/2016'
        ];
        yield [
            [
                'id' => 1,
                'status' => [
                    'description' => 'Expired'
                ],
                'futureSuspension' => null,
                'currentSuspension' => null,
                'currentWithdrawal' => null,
                'expiredDate' => '2016-01-01',
            ],
            'Expired: 01/01/2016'
        ];
        yield [
            [
                'id' => 1,
                'status' => [
                    'description' => 'Pending'
                ],
                'futureSuspension' => null,
                'currentSuspension' => null,
                'currentWithdrawal' => null,
            ],
            'Pending'
        ];
    }
}
