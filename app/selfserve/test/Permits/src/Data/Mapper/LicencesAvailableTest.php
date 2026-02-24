<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Elements\Types\Radio;
use Common\Form\Elements\Types\RadioVertical;
use Common\Form\Form;
use Mockery as m;
use Permits\Data\Mapper\LicencesAvailable;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Fieldset;

class LicencesAvailableTest extends TestCase
{
    private $licencesAvailable;

    public function setUp(): void
    {
        $this->licencesAvailable = new LicencesAvailable();
    }

    public function testMapForFormOptionsEcmtRestricted(): void
    {
        $inputData = [
            'licencesAvailable' => [
                'eligibleLicences' => [
                    7 => [
                        'id' => 7,
                        'licNo' => 'OB1234567',
                        'trafficArea' => 'North East of England',
                        'isRestricted' => true,
                        'licenceTypeDesc' => 'Restricted',
                        'canMakeApplication' => true,
                        'activeApplicationId' => null,
                    ],
                ],
                'selectedLicence' => 7,
                'isEcmtAnnual' => true,
            ]
        ];

        $valueOptions = [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Restricted (North East of England)',
                'selected' => true,
                'attributes' => [
                    'id' => 'licence'
                ]
            ],
        ];

        $mockRadio = m::mock(Radio::class);
        $mockRadio->shouldReceive('setValueOptions')->once()->with($valueOptions);

        $mockRadioVertical = m::mock(Fieldset::class);
        $mockRadioVertical->shouldReceive('get')->once()->with('licence')->andReturn($mockRadio);

        $mockRadioVertical
            ->shouldReceive('add')
            ->once()
            ->with(m::type(HtmlTranslated::class))
            ->andReturnUsing(
                function (HtmlTranslated $htmlTranslated) {
                    $this->assertEquals('7Content', $htmlTranslated->getName());
                    $this->assertEquals(
                        LicencesAvailable::ECMT_RESTRICTED_HINT,
                        $htmlTranslated->getValue()
                    );

                    return $htmlTranslated;
                }
            );

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockRadioVertical);

        $this->assertEquals(
            $inputData,
            $this->licencesAvailable->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testMultiLicence(): void
    {
        $inputData = [
            'licencesAvailable' => [
                'eligibleLicences' => [
                    7 => [
                        'id' => 7,
                        'licNo' => 'OB1234567',
                        'trafficArea' => 'North East of England',
                        'isRestricted' => false,
                        'licenceTypeDesc' => 'Standard National',
                        'canMakeApplication' => true,
                        'activeApplicationId' => null,
                    ],
                    406 => [
                        'id' => 406,
                        'licNo' => 'OB7654321',
                        'trafficArea' => 'Scotland',
                        'isRestricted' => false,
                        'licenceTypeDesc' => 'Standard International',
                        'canMakeApplication' => true,
                        'activeApplicationId' => null,
                    ],
                ],
                'selectedLicence' => 7,
                'isEcmtAnnual' => false,
            ]
        ];

        $valueOptions = [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard National (North East of England)',
                'selected' => true,
                'attributes' => [
                    'id' => 'licence'
                ]
            ],
            406 => [
                'value' => 406,
                'label' => 'OB7654321',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard International (Scotland)',
                'selected' => false,
            ],
        ];

        $mockRadio = m::mock(Radio::class);
        $mockRadio->shouldReceive('setValueOptions')->once()->with($valueOptions);

        $mockRadioVertical = m::mock(Fieldset::class);
        $mockRadioVertical->shouldReceive('get')->once()->with('licence')->andReturn($mockRadio);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockRadioVertical);

        $this->assertEquals(
            $inputData,
            $this->licencesAvailable->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testActiveWarning(): void
    {
        $inputData = [
            'licencesAvailable' => [
                'eligibleLicences' => [
                    7 => [
                        'id' => 7,
                        'licNo' => 'OB1234567',
                        'trafficArea' => 'North East of England',
                        'isRestricted' => false,
                        'licenceTypeDesc' => 'Standard National',
                        'canMakeApplication' => true,
                        'activeApplicationId' => 12345,
                    ],
                    406 => [
                        'id' => 406,
                        'licNo' => 'OB7654321',
                        'trafficArea' => 'Scotland',
                        'isRestricted' => false,
                        'licenceTypeDesc' => 'Standard International',
                        'canMakeApplication' => true,
                        'activeApplicationId' => null,
                    ],
                ],
                'selectedLicence' => 7,
                'isEcmtAnnual' => false,
            ],
            'active' => 7
        ];

        $valueOptions = [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard National (North East of England)',
                'selected' => true,
                'attributes' => [
                    'id' => 'licence'
                ]
            ],
            406 => [
                'value' => 406,
                'label' => 'OB7654321',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard International (Scotland)',
                'selected' => false,
            ],
        ];

        $outputData = $inputData;
        $outputData['warning'] = 'permits.irhp.bilateral.already-applied';

        $mockRadio = m::mock(Radio::class);
        $mockRadio->shouldReceive('setValueOptions')->once()->with($valueOptions);

        $mockRadioVertical = m::mock(Fieldset::class);
        $mockRadioVertical->shouldReceive('get')->once()->with('licence')->andReturn($mockRadio);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockRadioVertical);

        $this->assertEquals(
            $outputData,
            $this->licencesAvailable->mapForFormOptions($inputData, $mockForm)
        );
    }
}
