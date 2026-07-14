<?php

/**
 * IrhpPermitApplicationRefLink Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\IrhpPermitApplicationRefLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class IrhpPermitApplicationRefLinkTest extends MockeryTestCase
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
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'with value' => [
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
        ];
        yield 'empty value' => [
            null,
            ''
        ];
    }
}
