<?php

/**
 * Fee Url formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

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
class FeeUrlTest extends MockeryTestCase
{
    public $mockRouteMatch;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $mockUrlHelper;
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
        $this->sut = new FeeUrl($this->router, $this->request, $this->urlHelper);

        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $this->mockUrlHelper = m::mock();
        $this->request
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

        $this->router
            ->shouldReceive('match')
            ->with($this->request)
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
     * @group Formatters
     * @group FeeStatusFormatter
     *
     * @dataProvider provider
     */
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
     * @return array
     */
    public function provider()
    {
        return [
            'operator fee' => [
                [
                    'id' => '99',
                    'description' => 'operator fee',
                ],
                'operator/fees',
                'operator/fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">operator fee</a>',
            ],
            'licence fee' => [
                [
                    'id' => '99',
                    'description' => 'licence fee',
                ],
                'licence/fees',
                'licence/fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">licence fee</a>',
            ],
            'application fee' => [
                [
                    'id' => '99',
                    'description' => 'app fee',
                ],
                'lva-application/fees',
                'lva-application/fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">app fee</a>',
            ],
            'bus reg fee' => [
                [
                    'id' => '99',
                    'description' => 'bus reg fee',
                ],
                'licence/bus-fees',
                'licence/bus-fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">bus reg fee</a>',
            ],
            'ECMT fee link' => [
                [
                    'id' => '99',
                    'description' => 'ECMT fee',
                ],
                'licence/irhp-fees/table',
                'licence/irhp-fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">ECMT fee</a>',
            ],
            'IRHP fee link' => [
                [
                    'id' => '99',
                    'description' => 'IRHP fee',
                ],
                'licence/irhp-application-fees/table',
                'licence/irhp-application-fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee'],
                '<a class="govuk-link" href="the_url">IRHP fee</a>',
            ],
            'misc fee' => [
                [
                    'id' => '99',
                    'description' => 'misc fee',
                ],
                'admin-dashboard/admin-payment-processing/misc-fees',
                'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
                ['fee' => '99', 'action' => 'edit-fee', 'controller' => 'Admin\PaymentProcessingController'],
                '<a class="govuk-link" href="the_url">misc fee</a>',
            ],
            'dashboard fee link' => [
                [
                    'id' => '99',
                    'description' => 'my fee',
                ],
                'fees',
                'fees/pay',
                ['fee' => '99'],
                '<a class="govuk-link" href="the_url">my fee</a>',
            ],
        ];
    }
}
