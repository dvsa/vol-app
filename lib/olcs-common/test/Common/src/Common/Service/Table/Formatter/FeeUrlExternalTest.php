<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FeeUrlExternal;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Url External formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeUrlExternalTest extends MockeryTestCase
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
        $this->sut = new FeeUrlExternal($this->router, $this->request, $this->urlHelper);

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
    public function testFormat($data, $routeMatch, $expectedRoute, $expectedRouteParams, $expectedLink, $expectedUrl): void
    {
        $this->mockRouteMatch
            ->shouldReceive('getMatchedRouteName')
            ->andReturn($routeMatch);

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($expectedRoute, $expectedRouteParams, ['query' => ['foo' => 'bar']], true)
            ->andReturn($expectedUrl);

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
            'dashboard fee link' => [
                [
                    'id' => '99',
                    'description' => 'my fee',
                ],
                'fees',
                'fees/pay',
                ['fee' => '99'],
                '<a class="govuk-link" href="feeurl">my fee</a>',
                'feeurl'
            ],
            'dashboard late fee link' => [
                [
                    'id' => '99',
                    'description' => 'my fee',
                    'isExpiredForLicence' => 1
                ],
                'fees',
                'fees/late',
                ['fee' => '99'],
                '<a class="govuk-link" href="lateurl">my fee</a>',
                'lateurl'
            ]
        ];
    }
}
