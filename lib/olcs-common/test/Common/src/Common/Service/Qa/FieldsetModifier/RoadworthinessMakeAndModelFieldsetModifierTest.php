<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\FieldsetModifier;

use Common\Service\Qa\FieldsetModifier\Fieldsets;
use Common\Service\Qa\FieldsetModifier\RoadworthinessMakeAndModelFieldsetModifier;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;

/**
 * RoadworthinessMakeAndModelFieldsetModifierTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class RoadworthinessMakeAndModelFieldsetModifierTest extends MockeryTestCase
{
    private $fieldset;

    private $roadworthinessMakeAndModelFieldsetModifier;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldset = m::mock(Fieldset::class);

        $this->roadworthinessMakeAndModelFieldsetModifier = new RoadworthinessMakeAndModelFieldsetModifier();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpShouldModify')]
    public function testShouldModify($fieldsetName, $expectedShouldModify): void
    {
        $this->fieldset->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn($fieldsetName);

        $this->assertEquals(
            $expectedShouldModify,
            $this->roadworthinessMakeAndModelFieldsetModifier->shouldModify($this->fieldset)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{list{'fieldset40', true}, list{'fieldset47', true}, list{'fieldset39', false}, list{'fieldset48', false}}
     */
    public static function dpShouldModify(): \Iterator
    {
        yield [Fieldsets::ROADWORTHINESS_VEHICLE_MAKE_AND_MODEL, true];
        yield [Fieldsets::ROADWORTHINESS_TRAILER_MAKE_AND_MODEL, true];
        yield ['fieldset39', false];
        yield ['fieldset48', false];
    }

    public function testModify(): void
    {
        $text = m::mock(Text::class);

        $this->fieldset->shouldReceive('get')
            ->with('qaElement')
            ->andReturn($text);

        $text->shouldReceive('setAttribute')
            ->with('class', 'govuk-input govuk-input--width-50')
            ->once();

        $this->roadworthinessMakeAndModelFieldsetModifier->modify($this->fieldset);
    }
}
