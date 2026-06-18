<?php

/**
 * IrhpPermitApplicationRefLink Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\IrhpPermitApplicationRefLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IrhpPermitApplicationRefLinkTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->sut = new IrhpPermitApplicationRefLink($this->urlHelper);
    }

    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'licence/irhp-application',
                [
                    'action' => 'index',
                    'licence' => 100
                ]
            )
            ->times(isset($data) ? 1 : 0)
            ->andReturn('url');

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'with value' => [
                [
                    'irhpPermitApplication' => [
                        'relatedApplication' => [
                            'applicationRef' => 'app ref>',
                            'licence' => [
                                'id' => 100
                            ]
                        ]
                    ]
                ],
                '<a class="govuk-link" href="url">app ref&gt;</a>',
            ],
            'empty value' => [
                null,
                ''
            ]
        ];
    }
}
