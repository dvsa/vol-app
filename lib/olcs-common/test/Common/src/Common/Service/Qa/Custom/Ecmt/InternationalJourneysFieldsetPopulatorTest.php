<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\InternationalJourneysFieldsetPopulator;
use Common\Service\Qa\Custom\Ecmt\NiWarningConditionalAdder;
use Common\Service\Qa\RadioFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * InternationalJourneysFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class InternationalJourneysFieldsetPopulatorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTrueFalse')]
    public function testPopulate($showNiWarning): void
    {
        $radioOptions = [
            'radioKey1' => 'radioValue1',
            'radioKey2' => 'radioValue2'
        ];

        $options = [
            'showNiWarning' => $showNiWarning,
            'radio' => $radioOptions
        ];

        $expectedWarningVisibleParameters = [
            'name' => 'warningVisible',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 0
            ]
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($expectedWarningVisibleParameters)
            ->once();

        $niWarningConditionalAdder = m::mock(NiWarningConditionalAdder::class);
        $niWarningConditionalAdder->shouldReceive('addIfRequired')
            ->with($fieldset, $showNiWarning)
            ->once()
            ->globally()
            ->ordered();

        $radioFieldsetPopulator = m::mock(RadioFieldsetPopulator::class);
        $radioFieldsetPopulator->shouldReceive('populate')
            ->with($form, $fieldset, $radioOptions)
            ->once()
            ->globally()
            ->ordered();

        $internationalJourneysFieldsetPopulator = new InternationalJourneysFieldsetPopulator(
            $radioFieldsetPopulator,
            $niWarningConditionalAdder
        );

        $internationalJourneysFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * @return \Iterator<(int | string), array<bool>>
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public static function dpTrueFalse(): \Iterator
    {
        yield [true];
        yield [false];
    }
}
