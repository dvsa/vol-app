<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * CaseStayMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CaseAppealMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\CaseStayMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\CaseAppealMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderEmptyAppeal(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithAppeal(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                    ],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderWithOutcome(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => '',
                        'outcome' => 'FOO'
                    ],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderWithoutDecisionDate(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => 'FOO',
                        'outcome' => ''
                    ],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderWithoutDecisionDateAndOutcome(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => 'FOO',
                        'outcome' => 'XXXX'
                    ],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithWithdrawn(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'withdrawnDate' => '2011-01-01'
                    ],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithCaseClosed(): void
    {
        $data = [
            'cases' => [
                [
                    'closedDate' => '2015-05-05',
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => ''
                    ]
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithCaseNotClosed(): void
    {
        $data = [
            'cases' => [
                [ // closed case
                    'closedDate' => '2015-05-05',
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => ''
                    ]
                ],
                [ // open case
                    'closedDate' => null,
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                        'decisionDate' => '',
                    ]
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'cases' => [
                [
                    'appeal' => null,
                ],
                [
                    'id' => 234,
                    'appeal' => [
                        'appealDate' => '2015-08-17',
                    ],
                ]
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/case-appeal',
                ['caseId' => 234, 'appealDate' => new \DateTime('2015-08-17'), 'hideCaseLink' => false]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
