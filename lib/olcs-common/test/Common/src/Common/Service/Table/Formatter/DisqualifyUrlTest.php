<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Stdlib\ParametersInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Disqualify Url formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DisqualifyUrlTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $router;

    protected $request;

    protected $permissionService;

    protected $mockRouteMatch;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        // Upstream wont fix this, so we need to suppress the deprecation - https://github.com/laminas/laminas-stdlib/issues/85
        set_error_handler(function ($severity, $message, $file, $line) {
            if (strpos($message, 'Serializable') !== false) {
                return true;
            }
            return false;
        }, E_DEPRECATED);

        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->router = m::mock(TreeRouteStack::class);
        $this->request = m::mock(Request::class);
        $this->permissionService = m::mock(Permission::class);
        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);
        $this->sut = new DisqualifyUrl($this->urlHelper, $this->router, $this->request, $this->permissionService);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormatInternalReadOnly(): void
    {
        $this->permissionService->expects('isInternalReadOnly')->withNoArgs()->andReturnTrue();
        $data = ['disqualificationStatus' => 'foo>'];

        $this->assertEquals('foo&gt;', $this->sut->format($data));
    }

    /**
     * @dataProvider provider
     */
    public function testFormat($data, $routeMatch, $expectedRoute, $expectedRouteParams, $params, $expectedLink): void
    {
        $mockParams = m::mock(ParametersInterface::class);
        $mockParams->expects('toArray')->withNoArgs()->andReturn(['foo' => 'bar']);

        $this->request->shouldReceive('getQuery')->withNoArgs()->andReturn($mockParams);

        $this->router->expects('match')->with($this->request)->andReturn($this->mockRouteMatch);

        $this->permissionService->expects('isInternalReadOnly')->withNoArgs()->andReturnFalse();

        $this->mockRouteMatch
            ->shouldReceive('getMatchedRouteName')
            ->andReturn($routeMatch)
            ->once()
            ->shouldReceive('getParams')
            ->andReturn($params)
            ->once();

        if ($expectedRoute !== null) {
            $this->urlHelper
                ->shouldReceive('fromRoute')
                ->with($expectedRoute, $expectedRouteParams, ['query' => ['foo' => 'bar']], true)
                ->andReturn('the_url');
        }

        $this->assertEquals($expectedLink, $this->sut->format($data, []));
    }

    public function provider(): array
    {
        return [
            'licence' => [
                [
                    'id' => '99',
                    'disqualificationStatus' => 'foo_licence>',
                ],
                'lva-licence/people',
                'disqualify-person/licence',
                ['person' => '99', 'licence' => 1],
                ['licence' => 1],
                '<a href="the_url" class="govuk-link js-modal-ajax">foo_licence&gt;</a>',
            ],
            'application' => [
                [
                    'id' => '99',
                    'disqualificationStatus' => 'foo_application>',
                ],
                'lva-application/people',
                'disqualify-person/application',
                ['person' => '99', 'application' => 2],
                ['application' => 2],
                '<a href="the_url" class="govuk-link js-modal-ajax">foo_application&gt;</a>',
            ],
            'variation' => [
                [
                    'id' => '99',
                    'disqualificationStatus' => 'foo_variation>',
                ],
                'lva-variation/people',
                'disqualify-person/variation',
                ['person' => '99', 'variation' => 3],
                ['application' => 3],
                '<a href="the_url" class="govuk-link js-modal-ajax">foo_variation&gt;</a>',
            ],
            'operator' => [
                [
                    'personId' => '99',
                    'disqualificationStatus' => 'foo_operator>',
                ],
                'operator/people',
                'operator/disqualify_person',
                ['person' => '99'],
                [],
                '<a href="the_url" class="govuk-link js-modal-ajax">foo_operator&gt;</a>',
            ],
            'unknown' => [
                [
                    'id' => '99',
                    'disqualificationStatus' => 'foo_unknown>',
                ],
                'bar',
                null,
                null,
                null,
                '<a href="" class="govuk-link js-modal-ajax">foo_unknown&gt;</a>',
            ],
        ];
    }
}
