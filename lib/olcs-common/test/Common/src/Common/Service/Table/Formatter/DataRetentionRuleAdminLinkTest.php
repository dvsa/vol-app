<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DataRetentionRuleAdminLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DataRetentionRuleAdminLink test
 */
class DataRetentionRuleAdminLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new DataRetentionRuleAdminLink($this->urlHelper);
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
                'admin-dashboard/admin-data-retention/rule-admin',
                ['action' => 'edit', 'id' => 1]
            )
            ->andReturn('DATA_RETENTION_RULE_EDIT_URL');

        $this->assertEquals(
            '<a href="DATA_RETENTION_RULE_EDIT_URL" class="govuk-link js-modal-ajax">Test</a>',
            $this->sut->format($data, [])
        );
    }
}
