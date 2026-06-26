<?php

/**
 * Fee Id Url formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

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
class TransactionUrlTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $router;

    protected $request;

    protected $mockRouteMatch;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->router = m::mock(TreeRouteStack::class);
        $this->request = m::mock(Request::class);
        $this->sut = new TransactionUrl($this->router, $this->request, $this->urlHelper);

        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $this->request
            ->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('toArray')
                    ->once()
                    ->andReturn(['foo' => 'bar'])
                    ->getMock()
            )
            ->once();

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
