<?php

/**
 * Transaction fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TransactionFeeStatus;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Transaction fee status formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TransactionFeeStatusTest extends MockeryTestCase
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
        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $this->sut = new TransactionFeeStatus($router, $request, $this->urlHelper);

        $router
            ->shouldReceive('match')
            ->with($request)
            ->andReturn($this->mockRouteMatch);
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
    public function testFormat($data, $route, $expectedRouteParams, $expectedOutput): void
    {
        $this->mockRouteMatch
            ->shouldReceive('getMatchedRouteName')
            ->andReturn($route);

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($route, $expectedRouteParams, [], true)
            ->andReturn('the_url');

        $this->assertEquals($expectedOutput, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'standard' => [
            [
                'reversingTransaction' => null,
            ],
            null,
            null,
            'Applied',
        ];
        yield 'reversed' => [
            [
                'reversingTransaction' => [
                    'id' => 99,
                    'type' => RefData::TRANSACTION_TYPE_REVERSAL,
                ],
            ],
            '/foo/transaction',
            ['transaction' => 99, 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">Reversed</a>',
        ];
        yield 'refunded' => [
            [
                'reversingTransaction' => [
                    'id' => 99,
                    'type' => RefData::TRANSACTION_TYPE_REFUND,
                ],
            ],
            '/foo/transaction',
            ['transaction' => 99, 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">Refunded</a>',
        ];
        yield 'other' => [
            [
                'reversingTransaction' => [
                    'id' => 99,
                    'type' => RefData::TRANSACTION_TYPE_OTHER,
                ],
            ],
            '/foo/transaction',
            ['transaction' => 99, 'action' => 'edit-fee'],
            '<a class="govuk-link" href="the_url">Adjusted</a>',
        ];
    }
}
