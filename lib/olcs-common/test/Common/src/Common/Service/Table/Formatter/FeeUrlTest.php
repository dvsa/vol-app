<?php

/**
 * Fee Url formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FeeUrl;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Url formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class FeeUrlTest extends MockeryTestCase
{
    public $mockRouteMatch;
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $router = m::mock(TreeRouteStack::class);
        $request = m::mock(Request::class);
        $this->sut = new FeeUrl($router, $request, $this->urlHelper);

        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $mockUrlHelper = m::mock();
        $request
            ->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('toArray')
                    ->once()
                    ->andReturn(['foo' => 'bar'])
                    ->getMock()
            )
            ->once()
            ->getMock();

        $router
            ->shouldReceive('match')
            ->with($request)
            ->andReturn($this->mockRouteMatch)
            ->getMock();
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('FeeStatusFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $routeMatch, $expectedRoute, $expectedRouteParams, $expectedLink): void
    {
        $this->mockRouteMatch
            ->shouldReceive('getMatchedRouteName')
            ->andReturn($routeMatch);

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($expectedRoute, $expectedRouteParams, ['query' => ['foo' => 'bar']], true)
            ->andReturn('the_url');

        $this->assertEquals($expectedLink, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'operator fee' => [
            [
                'id' => '99',
                'description' => 'operator fee',
            ],
            'operator/fees',
            'operator/fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">operator fee</a>',
        ];
        yield 'licence fee' => [
            [
                'id' => '99',
                'description' => 'licence fee',
            ],
            'licence/fees',
            'licence/fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">licence fee</a>',
        ];
        yield 'application fee' => [
            [
                'id' => '99',
                'description' => 'app fee',
            ],
            'lva-application/fees',
            'lva-application/fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">app fee</a>',
        ];
        yield 'bus reg fee' => [
            [
                'id' => '99',
                'description' => 'bus reg fee',
            ],
            'licence/bus-fees',
            'licence/bus-fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">bus reg fee</a>',
        ];
        yield 'ECMT fee link' => [
            [
                'id' => '99',
                'description' => 'ECMT fee',
            ],
            'licence/irhp-fees/table',
            'licence/irhp-fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">ECMT fee</a>',
        ];
        yield 'IRHP fee link' => [
            [
                'id' => '99',
                'description' => 'IRHP fee',
            ],
            'licence/irhp-application-fees/table',
            'licence/irhp-application-fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">IRHP fee</a>',
        ];
        yield 'misc fee' => [
            [
                'id' => '99',
                'description' => 'misc fee',
            ],
            'admin-dashboard/admin-payment-processing/misc-fees',
            'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
            ['fee' => '99', 'action' => 'edit-fee', 'controller' => 'Admin\PaymentProcessingController'],
            '<a class="govuk-link" href="the_url">misc fee</a>',
        ];
        yield 'dashboard fee link' => [
            [
                'id' => '99',
                'description' => 'my fee',
            ],
            'fees',
            'fees/pay',
            ['fee' => '99'],
            '<a class="govuk-link" href="the_url">my fee</a>',
        ];
    }
}
