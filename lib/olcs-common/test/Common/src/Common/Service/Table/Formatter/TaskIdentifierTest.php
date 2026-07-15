<?php

/**
 * Task identifier formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\TaskIdentifier;
use Mockery as m;

/**
 * Task identifier formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TaskIdentifierTest extends \PHPUnit\Framework\TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new TaskIdentifier($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test link formatter
     */
    #[\PHPUnit\Framework\Attributes\Group('taskIdentifier')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat(
        $data,
        $column,
        $routeName,
        $param,
        $expected,
        $routeParams = []
    ): void {
        $routeParams = array_merge($routeParams, [$param => $data['linkId']]);

        $this->urlHelper->shouldReceive('fromRoute')
            ->with($routeName, $routeParams)
            ->andReturn('correctUrl');

        $result = $this->sut->format($data, $column);

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        // Licence
        yield 0 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Licence',
                'linkId' => null
            ],
            [],
            'lva-licence/overview',
            'licence',
            'Unlinked'
        ];
        yield 1 => [
            [
                'linkDisplay' => 'P1234',
                'linkType' => 'Licence',
                'linkId' => 1,
            ],
            [],
            'lva-licence/overview',
            'licence',
            '<a class="govuk-link" href="correctUrl">P1234</a>'
        ];
        yield 3 => [
            [
                'linkDisplay' => 'P1234',
                'linkType' => 'Licence',
                'linkId' => 1,
            ],
            [],
            'lva-licence/overview',
            'licence',
            '<a class="govuk-link" href="correctUrl">P1234</a>'
        ];
        yield 4 => [
            [
                'linkDisplay' => 'P1234',
                'linkType' => '',
                'linkId' => 1,
            ],
            [],
            'lva-licence/overview',
            'licence',
            '<a class="govuk-link" href="#">P1234</a>'
        ];
        // Application
        yield 5 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Application',
                'linkId' => null
            ],
            [],
            'lva-application/overview',
            'application',
            'Unlinked'
        ];
        yield 6 => [
            [
                'linkDisplay' => 'P1234',
                'linkType' => 'Application',
                'linkId' => 1,
            ],
            [],
            'lva-application/overview',
            'application',
            '<a class="govuk-link" href="correctUrl">P1234</a>'
        ];
        yield 7 => [
            [
                'linkDisplay' => 'P1234',
                'linkType' => '',
                'linkId' => 1,
            ],
            [],
            'lva-application/overview',
            'application',
            '<a class="govuk-link" href="#">P1234</a>'
        ];
        // Transport Manager
        yield 8 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Transport Manager',
                'linkId' => null,
            ],
            [],
            'lva-application/overview',
            'application',
            'Unlinked'
        ];
        yield 9 => [
            [
                'linkDisplay' => '1234',
                'linkType' => 'Transport Manager',
                'linkId' => 1,
            ],
            [],
            'transport-manager/details',
            'transportManager',
            '<a class="govuk-link" href="correctUrl">1234</a>'
        ];
        yield 10 => [
            [
                'linkDisplay' => '1234',
                'linkType' => '',
                'linkId' => 1,
            ],
            [],
            'transport-manager/details',
            'transportManager',
            '<a class="govuk-link" href="#">1234</a>'
        ];
        // Bus Registration
        yield 11 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Bus Registration',
                'linkId' => null,
            ],
            [],
            'licence/bus-details',
            'busRegId',
            'Unlinked'
        ];
        yield 12 => [
            [
                'linkDisplay' => 'P1234/123',
                'linkType' => 'Bus Registration',
                'linkId' => 99,
                'licenceId' => 110
            ],
            [],
            'licence/bus-details',
            'busRegId',
            '<a class="govuk-link" href="correctUrl">P1234/123</a>',
            ['licence' => 110] // additional route param needed
        ];
        yield 13 => [
            [
                'linkDisplay' => 'P1234/123',
                'linkType' => '',
                'linkId' => 99,
                'licenceId' => 110
            ],
            [],
            'licence/bus-details',
            'busRegId',
            '<a class="govuk-link" href="#">P1234/123</a>',
            ['licence' => 110]
        ];
        // Case
        yield 14 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Case',
                'linkId' => null,
            ],
            [],
            'case',
            'case',
            'Unlinked'
        ];
        yield 15 => [
            [
                'linkDisplay' => '1234',
                'linkType' => 'Case',
                'linkId' => 99,
            ],
            [],
            'case',
            'case',
            '<a class="govuk-link" href="correctUrl">1234</a>',
        ];
        yield 16 => [
            [
                'linkDisplay' => '1234',
                'linkType' => '',
                'linkId' => 99,
            ],
            [],
            'case',
            'case',
            '<a class="govuk-link" href="#">1234</a>',
        ];
        // IRFO Organisation
        yield 17 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'IRFO Organisation',
                'linkId' => null,
            ],
            [],
            'operator/business-details',
            'organisation',
            'Unlinked'
        ];
        yield 18 => [
            [
                'linkDisplay' => '1234',
                'linkType' => 'IRFO Organisation',
                'linkId' => 99,
            ],
            [],
            'operator/business-details',
            'organisation',
            '<a class="govuk-link" href="correctUrl">1234</a>',
        ];
        yield 19 => [
            [
                'linkDisplay' => '1234',
                'linkType' => '',
                'linkId' => 99,
            ],
            [],
            'operator/business-details',
            'organisation',
            '<a class="govuk-link" href="#">1234</a>',
        ];
        // Submission
        yield 20 => [
            [
                'linkDisplay' => 'Unlinked',
                'linkType' => 'Submission',
                'linkId' => null,
                'caseId' => 5
            ],
            [],
            'submission',
            'submission',
            'Unlinked',
            ['case' => 5, 'action' => 'details']
        ];
        yield 21 => [
            [
                'linkDisplay' => '1234/5',
                'linkType' => 'Submission',
                'linkId' => 99,
                'caseId' => 5
            ],
            [],
            'submission',
            'submission',
            '<a class="govuk-link" href="correctUrl">1234/5</a>',
            ['case' => 5, 'action' => 'details']
        ];
        yield 22 => [
            [
                'linkDisplay' => '1234/5',
                'linkType' => '',
                'linkId' => 99,
                'caseId' => 5
            ],
            [],
            'submission',
            'submission',
            '<a class="govuk-link" href="#">1234/5</a>',
            ['case' => 5, 'action' => 'details']
        ];
        // Permits
        yield 23 => [
            [
                'linkDisplay' => 'OG4569803/6',
                'linkType' => 'Permit Application',
                'linkId' => 6,
                'licenceId' => 106,
            ],
            [],
            'licence/irhp-application/application',
            'irhpAppId',
            '<a class="govuk-link" href="correctUrl">OG4569803/6</a>',
            [
                'irhpAppId' => 6,
                'licence' => 106,
                'action' => 'edit'
            ]
        ];
    }
}
