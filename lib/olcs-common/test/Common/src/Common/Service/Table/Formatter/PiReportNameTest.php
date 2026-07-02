<?php

/**
 * PI Report Name Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\OrganisationLink;
use Common\Service\Table\Formatter\PiReportName;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * PI Report Name Test
 *
 * @package CommonTest\Service\Table\Formatter
 */
class PiReportNameTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new PiReportName(new OrganisationLink($this->urlHelper), new Name(new DataHelperService()));
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'operator/business-details',
                [
                    'organisation' => 456,
                ]
            )
            ->andReturn('ORG_URL');

        $this->assertEquals(
            $expected,
            $this->sut->format($data, [])
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'licence' => [
                [
                    'pi' => [
                        'case' => [
                            'licence' => [
                                'organisation' => [
                                    'id' => 456,
                                    'name' => 'Org name',
                                ]
                            ]
                        ]
                    ],
                ],
                '<a class="govuk-link" href="ORG_URL">Org name</a>',
            ],
            'tm' => [
                [
                    'pi' => [
                        'case' => [
                            'transportManager' => [
                                'homeCd' => [
                                    'person' => [
                                        'forename' => 'TM',
                                        'familyName' => 'Name',
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                'TM Name',
            ],
            'other' => [
                [],
                '',
            ],
        ];
    }
}
