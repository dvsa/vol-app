<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;

/**
 * LicenceStatusRuleMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusRuleMarkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\LicenceStatusRuleMarker
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\LicenceStatusRuleMarker();
    }

    public function testCanRenderWithNoData()
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNotValid()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'xx']
            ],
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderRulesWrongStatus()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_valid'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'XXXlsts_curtailed']
                    ]
                ]
            ],
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNoRules()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'xx'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XX',
                        'licenceStatus' => ['id' => 'lsts_curtailed']
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'MADE_UP']
                    ],
                ]
            ],
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRender()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_valid'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XX',
                        'licenceStatus' => ['id' => 'lsts_curtailed']
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_curtailed']
                    ],
                ]
            ],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRenderCurtailedWithEndDate()
    {
        $data = [
            'licence' => [
                'id' => 345,
                'status' => ['id' => 'lsts_valid'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XX',
                        'licenceStatus' => ['id' => 'lsts_curtailed']
                    ],
                    [
                        'id' => 234,
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_curtailed'],
                        'startDate' => '2014-02-25',
                        'endDate' => '2014-12-10',
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status-rule',
                [
                    'startDateTime' => new \DateTime('2014-02-25'),
                    'endDateTime' => new \DateTime('2014-12-10'),
                    'status' => ['id' => 'lsts_curtailed'],
                    'ruleId' => 234,
                    'licenceId' => 345,
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderRevokedWithoutEndDate()
    {
        $data = [
            'licence' => [
                'id' => 345,
                'status' => ['id' => 'lsts_valid'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XX',
                        'licenceStatus' => ['id' => 'lsts_curtailed']
                    ],
                    [
                        'id' => 234,
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_revoked'],
                        'startDate' => '2014-02-25',
                        'endDate' => '',
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status-rule',
                [
                    'startDateTime' => new \DateTime('2014-02-25'),
                    'endDateTime' => null,
                    'status' => ['id' => 'lsts_revoked'],
                    'ruleId' => 234,
                    'licenceId' => 345,
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderRevokedWithStartProcessedDate()
    {
        $data = [
            'licence' => [
                'id' => 345,
                'status' => ['id' => 'lsts_valid'],
                'licenceStatusRules' => [
                    [
                        'id' => 234,
                        'startProcessedDate' => 'FOO',
                        'licenceStatus' => ['id' => 'lsts_revoked'],
                        'startDate' => '2014-02-25',
                        'endDate' => null,
                        'endProcessedDate' => null,
                    ],
                ]
            ],
        ];

        $this->sut->setData($data);
        $this->assertFalse($this->sut->canRender());
    }
}
