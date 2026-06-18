<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DataRetentionRuleLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DataRetentionRule Link test
 */
class DataRetentionRuleLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new DataRetentionRuleLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $data = [
            'id' => 1,
            'description' => 'test',
        ];

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'admin-dashboard/admin-data-retention/review/records',
                ['dataRetentionRuleId' => 1]
            )
            ->andReturn('DATA_RETENTION_EDIT_URL');

        $this->assertEquals(
            '<a class="govuk-link" href="DATA_RETENTION_EDIT_URL" target="_self">Test</a>',
            $this->sut->format($data, [])
        );
    }
}
