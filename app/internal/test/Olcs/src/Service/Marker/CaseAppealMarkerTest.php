<?php

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

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\CaseAppealMarker();
    }

    public function testCanRenderWithNoData()
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderEmptyAppeal()
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

    public function testCanRenderWithAppeal()
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

    public function testCanRenderWithOutcome()
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

    public function testCanRenderWithoutDecisionDate()
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

    public function testCanRenderWithoutDecisionDateAndOutcome()
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

    public function testCanRenderWithWithdrawn()
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

    public function testCanRenderWithCaseClosed()
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

    public function testCanRenderWithCaseNotClosed()
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

    public function testRender()
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

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

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
