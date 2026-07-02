<?php

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
class RoadworthinessMakeAndModelFieldsetModifierTest extends MockeryTestCase
{
    private $fieldset;

    private $roadworthinessMakeAndModelFieldsetModifier;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldset = m::mock(Fieldset::class);

        $this->roadworthinessMakeAndModelFieldsetModifier = new RoadworthinessMakeAndModelFieldsetModifier();
    }

    /**
     * @dataProvider dpShouldModify
     */
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
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'fieldset40', true}, list{'fieldset47', true}, list{'fieldset39', false}, list{'fieldset48', false}}
     */
    public function dpShouldModify(): array
    {
        return [
            [Fieldsets::ROADWORTHINESS_VEHICLE_MAKE_AND_MODEL, true],
            [Fieldsets::ROADWORTHINESS_TRAILER_MAKE_AND_MODEL, true],
            ['fieldset39', false],
            ['fieldset48', false],
        ];
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
