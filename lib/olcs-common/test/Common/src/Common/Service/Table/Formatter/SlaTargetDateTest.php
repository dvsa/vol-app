<?php

/**
 * SlaTargetDate formatter test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\SlaTargetDate;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SlaTargetDate formatter test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class SlaTargetDateTest extends TestCase
{
    public $mockRouteMatch;
    protected $urlHelper;

    protected $translator;

    protected $router;

    protected $request;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->router = m::mock(TreeRouteStack::class);
        $this->request = m::mock(Request::class);
        $this->sut = new SlaTargetDate($this->router, $this->request, $this->urlHelper, new Date());

        $this->mockRouteMatch = m::mock(\Laminas\Router\RouteMatch::class);

        $this->router
            ->shouldReceive('match')
            ->with($this->request)
            ->andReturn($this->mockRouteMatch)
            ->getMock();
    }

    /**
     * Test the format method
     *
     * @group Formatters
     * @group SlaTargetDateFormatter
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
            ->with($expectedRoute, $expectedRouteParams, [], true)
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
            'case-documents-no-target-date' => [
                [
                    'id' => 201,
                    'agreedDate' => '2001-01-01'
                ],
                'case/documents',
                'case/documents/edit-sla',
                ['entityType' => 'document', 'entityId' => 201],
                '<a href="the_url" class="govuk-link js-modal-ajax">Not set</a> ',
            ],
            'case-documents-target-date-set' => [
                [
                    'id' => 201,
                    'agreedDate' => '2001-01-01',
                    'targetDate' => '2001-02-02',
                    'sentDate' => '2001-01-01'
                ],
                'case/documents',
                'case/documents/edit-sla',
                ['entityType' => 'document', 'entityId' => 201],
                '<a href="the_url" class="govuk-link js-modal-ajax">02/02/2001</a> <span class="status green">Pass</span>',
            ],
            'case-documents-not-set' => [
                [
                    'id' => 201,
                    'agreedDate' => '',
                    'targetDate' => '2001-02-02',
                    'sentDate' => '2001-01-01'
                ],
                'case/documents',
                'case/documents/add-sla',
                ['entityType' => 'document', 'entityId' => 201],
                '<a href="the_url" class="govuk-link js-modal-ajax">Not set</a>',
            ]
        ];
    }
}
