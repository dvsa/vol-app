<?php

/**
 * PI Report Record Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\PiReportRecord;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * PI Report Record Test
 *
 * @package CommonTest\Service\Table\Formatter
 */
class PiReportRecordTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new PiReportRecord($this->urlHelper);
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
                        'licence',
                        [
                            'licence' => 123,
                        ]
                    )
                    ->andReturn('LIC_URL')
                    ->shouldReceive('fromRoute')
                    ->with(
                        'transport-manager/details',
                        [
                            'transportManager' => 3,
                        ]
                    )
                    ->andReturn('TM_URL');

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
                                'id' => 123,
                                'licNo' => 'AB1234567',
                                'status' => [
                                    'description' => 'lic status'
                                ]
                            ]
                        ]
                    ],
                ],
                '<a class="govuk-link" href="LIC_URL">AB1234567</a> (lic status)',
            ],
            'tm' => [
                [
                    'pi' => [
                        'case' => [
                            'transportManager' => [
                                'id' => 3,
                                'tmStatus' => [
                                    'description' => 'tm status'
                                ]
                            ]
                        ]
                    ],
                ],
                '<a class="govuk-link" href="TM_URL">TM 3</a> (tm status)',
            ],
            'other' => [
                [],
                '',
            ],
        ];
    }
}
