<?php

/**
 * Licence number and status test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\LicenceNumberAndStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Licence number and status test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class LicenceNumberAndStatusTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new LicenceNumberAndStatus($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test format
     *
     * @param array $data
     * @param string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with('lva-licence', ['licence' => 2])
            ->andReturn('lva-licence/2');

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'Valid' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_VALID,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Suspended' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_SUSPENDED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Curtailed' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_CURTAILED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Under consideration' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_UNDER_CONSIDERATION,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Granted' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_GRANTED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Surrender under consideration' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            'OB123',
        ];
        yield 'Surrendered' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_SURRENDERED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Revoked' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_REVOKED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Terminated' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_TERMINATED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'CNS' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Withdrawn' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_WITHDRAWN,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Refused' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_REFUSED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Not taken up' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_NOT_TAKEN_UP,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Cancelled' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_CANCELLED,
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Unknown' => [
            [
                'status' => [
                    'id' => 'unknown',
                ],
                'licNo' => 'OB123',
                'id' => 2
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Expired' => [
            [
                'status' => [
                    'id' => 'unknown',
                ],
                'licNo' => 'OB123',
                'id' => 2,
                'isExpired' => true,
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Expiring' => [
            [
                'status' => [
                    'id' => 'unknown',
                    'description' => 'Unknown'
                ],
                'licNo' => 'OB123',
                'id' => 2,
                'isExpiring' => true,
            ],
            '<a class="govuk-link" href="lva-licence/2">OB123</a>',
        ];
        yield 'Expiring but Surrendered' => [
            [
                'status' => [
                    'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                    'description' => 'Surrender under consideration'
                ],
                'licNo' => 'OB123',
                'id' => 2,
                'isExpiring' => true,
            ],
            'OB123',
        ];
    }
}
