<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;

/**
 * LicenceStatusMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusMarkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\LicenceStatusMarker
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\LicenceStatusMarker();
    }

    public function testCanRenderWithNoData()
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderCurtailed()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_curtailed']
            ],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderSuspended()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_suspended']
            ],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderRevoked()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_revoked']
            ],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderOther()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'XXXX']
            ],
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testRenderWithRule()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_curtailed'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XXXXX',
                        'licenceStatus' => 'lsts_curtailed',
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => 'XXXX',
                    ],
                    [
                        'startDate' => '2014-04-16',
                        'endDate' => '2015-08-22',
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_curtailed'],
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => new \DateTime('2014-04-16'),
                    'endDateTime' => new \DateTime('2015-08-22'),
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderWithRuleNoEndDate()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_curtailed'],
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XXXXX',
                        'licenceStatus' => 'lsts_curtailed',
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => 'XXXX',
                    ],
                    [
                        'startDate' => '2013-04-16',
                        'endDate' => '',
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_curtailed'],
                    ],
                    [
                        'startDate' => '2014-04-16',
                        'endDate' => '',
                        'endProcessedDate' => '',
                        'licenceStatus' => ['id' => 'lsts_curtailed'],
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => new \DateTime('2014-04-16'),
                    'endDateTime' => null,
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderWithOutRuleCurtailed()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_curtailed'],
                'curtailedDate' => '2014-06-23',
                'revokedDate' => '2015-12-16',
                'suspendedDate' => '2014-04-19',
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XXXXX',
                        'licenceStatus' => 'lsts_curtailed',
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => 'XXXX',
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => new \DateTime('2014-06-23'),
                    'endDateTime' => null,
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderWithOutRuleRevoked()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_revoked'],
                'curtailedDate' => '2014-06-23',
                'revokedDate' => '2015-12-16',
                'suspendedDate' => '2014-04-19',
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XXXXX',
                        'licenceStatus' => 'lsts_curtailed',
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => 'XXXX',
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => new \DateTime('2015-12-16'),
                    'endDateTime' => null,
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderWithOutRuleSuspended()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_suspended'],
                'curtailedDate' => '2014-06-23',
                'revokedDate' => '2015-12-16',
                'suspendedDate' => '2014-04-19',
                'licenceStatusRules' => [
                    [
                        'endProcessedDate' => 'XXXXX',
                        'licenceStatus' => 'lsts_curtailed',
                    ],
                    [
                        'endProcessedDate' => '',
                        'licenceStatus' => 'XXXX',
                    ],
                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => new \DateTime('2014-04-19'),
                    'endDateTime' => null,
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderWithMissingDate()
    {
        $data = [
            'licence' => [
                'status' => ['id' => 'lsts_revoked'],
                'revokedDate' => null,
                'licenceStatusRules' => []
            ],
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/licence-status',
                [
                    'startDateTime' => null,
                    'endDateTime' => null,
                    'status' => $data['licence']['status'],
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
