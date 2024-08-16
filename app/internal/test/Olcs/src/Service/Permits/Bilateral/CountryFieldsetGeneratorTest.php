<?php

/**
 * CountryFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Service\Permits\Bilateral\CountryFieldsetGenerator;
use Olcs\Service\Permits\Bilateral\PeriodFieldsetGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Element\Select;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\Fieldset;

/**
 * CountryFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class CountryFieldsetGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $countryId = 'NO';
        $countryName = 'Norway';
        $countryType = 'country.type';
        $countryPeriodLabel = 'country.period.label';
        $selectedPeriodId = 44;

        $period44Id = 44;
        $period44TranslationKey = 'period.key.44';
        $period44Name = 'period 44 name';
        $period44Data = [
            'id' => $period44Id,
            'key' => $period44TranslationKey
        ];

        $period45Id = 45;
        $period45TranslationKey = 'period.key.45';
        $period45Name = 'period 45 name';
        $period45Data = [
            'id' => $period45Id,
            'key' => $period45TranslationKey
        ];

        $countryData = [
            'id' => $countryId,
            'name' => $countryName,
            'type' => $countryType,
            'periodLabel' => $countryPeriodLabel,
            'periods' => [
                $period44Data,
                $period45Data
            ],
            'selectedPeriodId' => $selectedPeriodId
        ];

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with($period44TranslationKey)
            ->andReturn($period44Name);
        $translator->shouldReceive('translate')
            ->with($period45TranslationKey)
            ->andReturn($period45Name);

        $formFactory = m::mock(FormFactory::class);

        $periodSelectorSelect = m::mock(Select::class);
        $periodSelectorSelectParams = [
            'type' => Select::class,
            'name' => 'selectedPeriodId',
            'options' => [
                'label' => $countryPeriodLabel,
                'value_options' => [
                    '' => $countryPeriodLabel,
                    $period44Id => $period44Name,
                    $period45Id => $period45Name
                ]
            ],
            'attributes' => [
                'value' => $selectedPeriodId
            ]
        ];

        $period44Fieldset = m::mock(Fieldset::class);
        $period45Fieldset = m::mock(Fieldset::class);

        $periodsFieldset = m::mock(Fieldset::class);
        $periodsFieldset->shouldReceive('add')
            ->with($period44Fieldset)
            ->once()
            ->ordered();
        $periodsFieldset->shouldReceive('add')
            ->with($period45Fieldset)
            ->once()
            ->ordered();

        $periodsFieldsetParams = [
            'type' => Fieldset::class,
            'name' => 'periods'
        ];

        $countryFieldset = m::mock(Fieldset::class);
        $countryFieldset->shouldReceive('add')
            ->with($periodSelectorSelect)
            ->once()
            ->ordered();
        $countryFieldset->shouldReceive('add')
            ->with($periodsFieldset)
            ->once()
            ->ordered();

        $countryFieldsetParams = [
            'type' => Fieldset::class,
            'name' => $countryId,
            'options' => [
                'label' => $countryName
            ],
            'attributes' => [
                'data-role' => 'country',
                'data-type' => $countryType,
                'data-id' => $countryId,
                'data-name' => $countryName
            ]
        ];

        $formFactory->shouldReceive('create')
            ->with($countryFieldsetParams)
            ->andReturn($countryFieldset);
        $formFactory->shouldReceive('create')
            ->with($periodSelectorSelectParams)
            ->andReturn($periodSelectorSelect);
        $formFactory->shouldReceive('create')
            ->with($periodsFieldsetParams)
            ->andReturn($periodsFieldset);

        $periodFieldsetGenerator = m::mock(PeriodFieldsetGenerator::class);
        $periodFieldsetGenerator->shouldReceive('generate')
            ->with($period44Data, $countryType)
            ->andReturn($period44Fieldset);
        $periodFieldsetGenerator->shouldReceive('generate')
            ->with($period45Data, $countryType)
            ->andReturn($period45Fieldset);

        $countryFieldsetGenerator = new CountryFieldsetGenerator($translator, $formFactory, $periodFieldsetGenerator);

        $this->assertSame(
            $countryFieldset,
            $countryFieldsetGenerator->generate($countryData)
        );
    }
}
