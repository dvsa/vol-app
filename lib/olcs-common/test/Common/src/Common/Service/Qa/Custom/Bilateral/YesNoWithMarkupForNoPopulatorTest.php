<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\Elements\InputFilters\QaRadio;
use Common\Service\Qa\Custom\Bilateral\YesNoRadioOptionsApplier;
use Common\Service\Qa\Custom\Bilateral\YesNoWithMarkupForNoPopulator;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\RadioFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * YesNoWithMarkupForNoPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class YesNoWithMarkupForNoPopulatorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpPopulate')]
    public function testPopulate($yesNo, $expectedYesNoValue): void
    {
        $notSelectedMessage = 'not.selected.message';

        $yesNoRadio = m::mock(QaRadio::class);

        $valueOptions = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $radioFactory = m::mock(RadioFactory::class);
        $radioFactory->shouldReceive('create')
            ->with('qaElement')
            ->andReturn($yesNoRadio);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($yesNoRadio)
            ->once();
        $fieldset->shouldReceive('setOption')
            ->with('radio-element', 'qaElement')
            ->once();

        $valueOptions = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $yesNoRadioOptionsApplier = m::mock(YesNoRadioOptionsApplier::class);
        $yesNoRadioOptionsApplier->shouldReceive('applyTo')
            ->with($yesNoRadio, $valueOptions, $expectedYesNoValue, $notSelectedMessage)
            ->once();

        $htmlAdder = m::mock(HtmlAdder::class);
        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'noContent', '<div class="govuk-hint"><p>No markup</p></div>')
            ->once();

        $yesNoWithMarkupForNoPopulator = new YesNoWithMarkupForNoPopulator(
            $radioFactory,
            $yesNoRadioOptionsApplier,
            $htmlAdder
        );

        $yesNoWithMarkupForNoPopulator->populate(
            $fieldset,
            $valueOptions,
            '<p>No markup</p>',
            $yesNo,
            $notSelectedMessage
        );
    }

    /**
     * @return \Iterator<(int | string), array<(string | null)>>
     *
     * @psalm-return list{list{'answer.value', 'Y'}, list{null, null}}
     */
    public static function dpPopulate(): \Iterator
    {
        yield ['answer.value', 'Y'];
        yield [null, null];
    }
}
