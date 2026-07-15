<?php

/**
 * Fee Id Url formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TransactionUrl;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Id Url formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class TransactionUrlTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $mockRouteMatch;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $router = m::mock(TreeRouteStack::class);
        $request = m::mock(Request::class);
        $this->sut = new TransactionUrl($router, $request, $this->urlHelper);

        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $request
            ->shouldReceive('getQuery')
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
     */
    public function testFormat(): void
    {
        $this->mockRouteMatch
            ->shouldReceive('getMatchedRouteName')
            ->andReturn('licence/fee');

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'licence/fee/transaction',
                ['transaction' => 1],
                ['query' => ['foo' => 'bar']],
                true
            )
            ->andReturn('the_url');

        $this->assertEquals('<a class="govuk-link" href="the_url">1</a>', $this->sut->format(['transactionId' => 1], []));
    }
}
