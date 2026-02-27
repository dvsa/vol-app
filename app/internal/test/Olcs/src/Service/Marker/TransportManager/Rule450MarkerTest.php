<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Rule50MarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Rule450MarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\TransportManager\Rule50Marker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\TransportManager\Rule450Marker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'transportManagerApplications' => [
                // just under both limits
                [
                    'transportManager' => [
                        'id' => 101,
                        'tmType' => ['id' => 'tm_t_b'],
                        'homeCd' => ['person' => 'PERSON1'],
                        'associatedOrganisationCount' => 4,
                        'associatedTotalAuthVehicles' => 50,
                    ]
                ],
                // not TM type external or both
                [
                    'transportManager' => [
                        'id' => 102,
                        'tmType' => ['id' => 'tm_t_i'],
                        'homeCd' => ['person' => 'PERSON2'],
                        'associatedOrganisationCount' => 14,
                        'associatedTotalAuthVehicles' => 119,
                    ]
                ],
                // over org limit
                [
                    'transportManager' => [
                        'id' => 103,
                        'tmType' => ['id' => 'tm_t_b'],
                        'homeCd' => ['person' => 'PERSON3'],
                        'associatedOrganisationCount' => 5,
                        'associatedTotalAuthVehicles' => 50,
                    ]
                ],
                // over tot vehicle limit
                [
                    'transportManager' => [
                        'id' => 104,
                        'tmType' => ['id' => 'tm_t_b'],
                        'homeCd' => ['person' => 'PERSON4'],
                        'associatedOrganisationCount' => 4,
                        'associatedTotalAuthVehicles' => 51,
                    ]
                ],
            ],
            'transportManagerLicences' => [
                // TML over both limits
                [
                    'transportManager' => [
                        'id' => 105,
                        'tmType' => ['id' => 'tm_t_e'],
                        'homeCd' => ['person' => 'PERSON5'],
                        'associatedOrganisationCount' => 8,
                        'associatedTotalAuthVehicles' => 74,
                    ]
                ],
                // duplicate of TM on App
                [
                    'transportManager' => [
                        'id' => 104,
                        'tmType' => ['id' => 'tm_t_b'],
                        'homeCd' => ['person' => 'PERSON4'],
                        'associatedOrganisationCount' => 4,
                        'associatedTotalAuthVehicles' => 51,
                    ]
                ],
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/rule450',
                [
                    'person' => 'PERSON3',
                    'associatedOrganisationCount' => 5,
                    'associatedTotalAuthVehicles' => 50,
                    'hideName' => true,
                ]
            )->once()->andReturn('HTML3');
        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/rule450',
                [
                    'person' => 'PERSON4',
                    'associatedOrganisationCount' => 4,
                    'associatedTotalAuthVehicles' => 51,
                    'hideName' => true,
                ]
            )->once()->andReturn('HTML4');
        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/rule450',
                [
                    'person' => 'PERSON5',
                    'associatedOrganisationCount' => 8,
                    'associatedTotalAuthVehicles' => 74,
                    'hideName' => true,
                ]
            )->once()->andReturn('HTML5');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML3HTML4HTML5', $this->sut->render());
    }
}
