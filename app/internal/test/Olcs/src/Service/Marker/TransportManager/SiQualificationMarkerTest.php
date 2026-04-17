<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker\TransportManager;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SiQualificationMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SiQualificationMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\TransportManager\Rule50Marker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\TransportManager\SiQualificationMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithGb(): void
    {
        $data = [
            'transportManagerApplications' => [
                [
                    'transportManager' => [
                        'id' => 103,
                        'homeCd' => ['person' => 'PERSON3'],
                        'requireSiGbQualification' => true,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => false,
                        'hasValidSiNiQualification' => false,
                    ]
                ],
            ],
            'transportManagersFromLicence' => [
                [
                    'transportManager' => [
                        'id' => 104,
                        'homeCd' => ['person' => 'PERSON3'],
                        'requireSiGbQualification' => true,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => false,
                        'hasValidSiNiQualification' => false,
                        'requireSiGbQualificationOnVariation' => true,
                        'requireSiNiQualificationOnVariation' => false,
                    ]
                ],
            ]
        ];
        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testCanRenderWithNi(): void
    {
        $data = [
            'transportManagerApplications' => [
                [
                    'transportManager' => [
                        'id' => 103,
                        'homeCd' => ['person' => 'PERSON3'],
                        'requireSiGbQualification' => false,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => true,
                        'hasValidSiNiQualification' => false,
                    ]
                ],
            ],
            'transportManagersFromLicence' => [
                [
                    'transportManager' => [
                        'id' => 104,
                        'homeCd' => ['person' => 'PERSON4'],
                        'requireSiGbQualification' => false,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => false,
                        'hasValidSiNiQualification' => false,
                        'requireSiNiQualificationOnVariation' => true,
                        'requireSiGbQualificationOnVariation' => false
                    ]
                ],
            ]
        ];
        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'transportManagerApplications' => [
                // requires both, has both
                [
                    'transportManager' => [
                        'id' => 101,
                        'homeCd' => ['person' => 'PERSON1'],
                        'requireSiGbQualification' => true,
                        'hasValidSiGbQualification' => true,
                        'requireSiNiQualification' => true,
                        'hasValidSiNiQualification' => true,
                    ]
                ],
                // requires none, has none
                [
                    'transportManager' => [
                        'id' => 102,
                        'homeCd' => ['person' => 'PERSON2'],
                        'requireSiGbQualification' => false,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => false,
                        'hasValidSiNiQualification' => false,
                    ]
                ],
            ],
            'transportManagerLicences' => [
                // requires GB, hasn't got GB
                [
                    'transportManager' => [
                        'id' => 103,
                        'homeCd' => ['person' => 'PERSON3'],
                        'requireSiGbQualification' => true,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => false,
                        'hasValidSiNiQualification' => false,
                    ]
                ],
                // requires NI, hasn't got NI
                [
                    'transportManager' => [
                        'id' => 104,
                        'homeCd' => ['person' => 'PERSON4'],
                        'requireSiGbQualification' => false,
                        'hasValidSiGbQualification' => false,
                        'requireSiNiQualification' => true,
                        'hasValidSiNiQualification' => false,
                    ]
                ],
            ],
            'page' => 'transportManager'
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/si-gb-qualification',
                [
                    'person' => 'PERSON3',
                    'niFlag' => false,
                    'hideName' => true,
                ]
            )->once()->andReturn('HTML3');
        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/si-gb-qualification',
                [
                    'person' => 'PERSON4',
                    'niFlag' => true,
                    'hideName' => true,
                ]
            )->once()->andReturn('HTML4');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML3HTML4', $this->sut->render());
    }
}
