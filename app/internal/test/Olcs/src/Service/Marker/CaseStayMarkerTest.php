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
class CaseStayMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\CaseStayMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\CaseStayMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderEmptyStays(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => [],
                    'appeal' => [
                        'decisionDate' => 'FOO',
                    ]
                ],
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithoutAppeal(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => ['STAY1', 'STAY2'],
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderAppealDecision(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => ['STAY1', 'STAY2'],
                    'appeal' => [
                        'decisionDate' => 'FOO',
                        'outcome' => 'FOO'
                    ]
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderAppealWithdrawn(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => ['STAY1', 'STAY2'],
                    'appeal' => [
                        'withdrawnDate' => 'FOO'
                    ]
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRender(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => ['STAY1', 'STAY2'],
                    'appeal' => [
                        'outcome' => 'FOO'
                    ]
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderWidthdrawnStays(): void
    {
        $data = [
            'cases' => [
                [
                    'stays' => [['withdrawnDate' => 'FOO'], ['withdrawnDate' => 'FOO']],
                    'appeal' => [
                        'outcome' => 'FOO'
                    ]
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'cases' => [
                [
                    'id' => 99,
                    'stays' => ['STAY1', 'STAY2'],
                    'appeal' => ['FOO']
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/case-stay', ['caseId' => 99, 'stay' => 'STAY1', 'hideCaseLink' => false])
            ->once()->andReturn('HTML1');
        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/case-stay', ['caseId' => 99, 'stay' => 'STAY2', 'hideCaseLink' => false])
            ->once()->andReturn('HTML2');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1HTML2', $this->sut->render());
    }

    public function testRenderOnWithdrawnStays(): void
    {
        $data = [
            'cases' => [
                [
                    'id' => 99,
                    'stays' => [['withdrawnDate' => 'FOO'], 'STAY2'],
                    'appeal' => ['FOO']
                ],
                [
                    'stays' => [],
                ]
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/case-stay', ['caseId' => 99, 'stay' => 'STAY2', 'hideCaseLink' => false])
            ->once()->andReturn('HTML2');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML2', $this->sut->render());
    }
}
