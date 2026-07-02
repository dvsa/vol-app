<?php

/**
 * SeriousInfringementLinkTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\SeriousInfringementLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SeriousInfringementLinkTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
class SeriousInfringementLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new SeriousInfringementLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $id = 69;
        $inputData = ['id' => $id];
        $route = 'case_penalty_applied';
        $routeParams = [
            'si' => $id,
            'action' => 'index'
        ];

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with($route, $routeParams, [], true)
            ->andReturn('URL');

        $this->assertEquals(
            '<a class="govuk-link" href="URL">' . $id . '</a>',
            $this->sut->format($inputData, [])
        );
    }
}
