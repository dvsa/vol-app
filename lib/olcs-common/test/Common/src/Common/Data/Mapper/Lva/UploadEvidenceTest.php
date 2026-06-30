<?php

namespace CommonTest\Data\Mapper\Lva;

use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Data\Mapper\Lva\UploadEvidence;
use Common\Form\Form;
use Mockery as m;

class UploadEvidenceTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'operatingCentres' => [
                [
                    'id' => 1,
                    'adPlacedIn' => 'foo',
                    'adPlacedDate' => '2017-12-01',
                ]
            ]
        ];
        $expected = [
            'operatingCentres' => [
                [
                    'adPlacedIn' => 'foo',
                    'aocId' => 1,
                    'adPlacedDate' => [
                        'day' => 1,
                        'month' => 12,
                        'year' => 2017
                    ]
                ]
            ]
        ];

        $output = UploadEvidence::mapFromResult($input);

        $this->assertEquals($expected, $output);
    }

    public function testMapFromFormNoOperatingCentres(): void
    {
        $this->assertEquals(['supportingEvidence' => false], UploadEvidence::mapFromForm([]));
    }

    public function testMapFromFormNoOperatingCentresAndNoSupportIngEvidence(): void
    {
        $this->assertEquals(['supportingEvidence' => false], UploadEvidence::mapFromForm([]));
    }

    public function testMapFromForm(): void
    {
        $input = [
            'operatingCentres' => [
                [
                    'aocId' => 1,
                    'adPlacedIn' => 'foo',
                    'adPlacedDate' => '2017-12-01',
                ]
            ]
        ];
        $expected = [
            'operatingCentres' => [
                1 => [
                    'adPlacedIn' => 'foo',
                    'aocId' => 1,
                    'adPlacedDate' => '2017-12-01'
                ]
            ],
            'supportingEvidence' => false
        ];

        $output = UploadEvidence::mapFromForm($input);

        $this->assertEquals($expected, $output);
    }

    public function testMapFromResultForm(): void
    {
        $data = [
            'operatingCentres' => [
                [
                        'id' => 1,
                        'adPlacedIn' => 'foo',
                        'adPlacedDate' => '2017-12-01',
                        'operatingCentre' => [
                            'address' => [
                            'town' => 'bar',
                            'postcode' => 'cake'
                            ]
                        ]
                ],
            ],
              'supportingEvidence' => false
        ];
        $mappedData = [
            'operatingCentres' => [
                [
                        'adPlacedIn' => 'foo',
                        'aocId' => 1,
                        'adPlacedDate' => [
                            'day' => 1,
                            'month' => 12,
                            'year' => 2017
                        ]
                ]
            ]
        ];

        $label = 'bar, cake';

        $fieldset = m::mock(Fieldset::class)
            ->shouldReceive('setLabel')
            ->with($label)
            ->once()
            ->getMock();

        $fieldsets = [$fieldset];

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('operatingCentres')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('getFieldsets')
                ->andReturn($fieldsets)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('setData')
            ->with($mappedData)
            ->once()
            ->getMock();

        UploadEvidence::mapFromResultForm($data, $mockForm);
    }

/**
 * @dataProvider dpSupportingEvidenceProvider
 */
    public function testMapFromFormWithSupportIngEvidence($inputData, $expectedData): void
    {
        $input = [
            'supportingEvidence' => $inputData
        ];
        $expected = [
            'supportingEvidence' => $expectedData
        ];
        $this->assertEquals($expected, UploadEvidence::mapFromForm($input));
    }

    public function dpSupportingEvidenceProvider(): array
    {
        return [
            [  ["something here .."],
                true],
            [  [],
                false],
        ];
    }
}
