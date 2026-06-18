<?php

/**
 * Task identifier formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

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
class TaskIdentifierTest extends \PHPUnit\Framework\TestCase
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
     * @group taskIdentifier
     * @dataProvider provider
     */
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
     * @return array
     */
    public function provider()
    {
        return [
            // Licence
            0 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'Licence',
                    'linkId' => null
                ],
                [],
                'lva-licence/overview',
                'licence',
                'Unlinked'
            ],
            1 => [
                [
                    'linkDisplay' => 'P1234',
                    'linkType' => 'Licence',
                    'linkId' => 1,
                ],
                [],
                'lva-licence/overview',
                'licence',
                '<a class="govuk-link" href="correctUrl">P1234</a>'
            ],
            3 => [
                [
                    'linkDisplay' => 'P1234',
                    'linkType' => 'Licence',
                    'linkId' => 1,
                ],
                [],
                'lva-licence/overview',
                'licence',
                '<a class="govuk-link" href="correctUrl">P1234</a>'
            ],
            4 => [
                [
                    'linkDisplay' => 'P1234',
                    'linkType' => '',
                    'linkId' => 1,
                ],
                [],
                'lva-licence/overview',
                'licence',
                '<a class="govuk-link" href="#">P1234</a>'
            ],
            // Application
            5 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'Application',
                    'linkId' => null
                ],
                [],
                'lva-application/overview',
                'application',
                'Unlinked'
            ],
            6 => [
                [
                    'linkDisplay' => 'P1234',
                    'linkType' => 'Application',
                    'linkId' => 1,
                ],
                [],
                'lva-application/overview',
                'application',
                '<a class="govuk-link" href="correctUrl">P1234</a>'
            ],
            7 => [
                [
                    'linkDisplay' => 'P1234',
                    'linkType' => '',
                    'linkId' => 1,
                ],
                [],
                'lva-application/overview',
                'application',
                '<a class="govuk-link" href="#">P1234</a>'
            ],
            // Transport Manager
            8 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'Transport Manager',
                    'linkId' => null,
                ],
                [],
                'lva-application/overview',
                'application',
                'Unlinked'
            ],
            9 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => 'Transport Manager',
                    'linkId' => 1,
                ],
                [],
                'transport-manager/details',
                'transportManager',
                '<a class="govuk-link" href="correctUrl">1234</a>'
            ],
            10 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => '',
                    'linkId' => 1,
                ],
                [],
                'transport-manager/details',
                'transportManager',
                '<a class="govuk-link" href="#">1234</a>'
            ],
            // Bus Registration
            11 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'Bus Registration',
                    'linkId' => null,
                ],
                [],
                'licence/bus-details',
                'busRegId',
                'Unlinked'
            ],
            12 => [
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
            ],
            13 => [
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
            ],
            // Case
            14 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'Case',
                    'linkId' => null,
                ],
                [],
                'case',
                'case',
                'Unlinked'
            ],
            15 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => 'Case',
                    'linkId' => 99,
                ],
                [],
                'case',
                'case',
                '<a class="govuk-link" href="correctUrl">1234</a>',
            ],
            16 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => '',
                    'linkId' => 99,
                ],
                [],
                'case',
                'case',
                '<a class="govuk-link" href="#">1234</a>',
            ],
            // IRFO Organisation
            17 => [
                [
                    'linkDisplay' => 'Unlinked',
                    'linkType' => 'IRFO Organisation',
                    'linkId' => null,
                ],
                [],
                'operator/business-details',
                'organisation',
                'Unlinked'
            ],
            18 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => 'IRFO Organisation',
                    'linkId' => 99,
                ],
                [],
                'operator/business-details',
                'organisation',
                '<a class="govuk-link" href="correctUrl">1234</a>',
            ],
            19 => [
                [
                    'linkDisplay' => '1234',
                    'linkType' => '',
                    'linkId' => 99,
                ],
                [],
                'operator/business-details',
                'organisation',
                '<a class="govuk-link" href="#">1234</a>',
            ],
            // Submission
            20 => [
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
            ],
            21 => [
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
            ],
            22 => [
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
            ],
            // Permits
            23 => [
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
            ],
        ];
    }
}
