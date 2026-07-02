<?php

/**
 * SystemParameter Link test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\SystemParameterLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SystemParameter Link test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SystemParameterLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new SystemParameterLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $data = [
            'id' => 1
        ];

        $this->urlHelper
                    ->shouldReceive('fromRoute')
                    ->with(
                        'admin-dashboard/admin-system-parameters',
                        [
                            'action' => 'edit',
                            'sp' => 1
                        ]
                    )
                    ->andReturn('SYSTEM_PARAMETER_EDIT_URL');

        $this->assertEquals(
            '<a href="SYSTEM_PARAMETER_EDIT_URL" class="govuk-link js-modal-ajax">1</a>',
            $this->sut->format($data, [])
        );
    }
}
