<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\FieldsetAdder;
use Common\Service\Qa\FieldsetFactory;
use Common\Service\Qa\FieldsetModifier\FieldsetModifier;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\FieldsetPopulatorProvider;
use Common\Service\Qa\UsageContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * FieldsetAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FieldsetAdderTest extends MockeryTestCase
{
    public const FIELDSET_NAME = 'fields123';

    public const ELEMENT_TYPE = 'elementType';

    public const SHORT_NAME = 'Cabotage';

    public const ELEMENT_OPTIONS = [
        'elementProperty1' => 'elementValue1',
        'elementProperty2' => 'elementValue2'
    ];

    private $fieldset;

    private $form;

    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldset = m::mock(Fieldset::class);

        $qaWrapperFieldset = m::mock(Fieldset::class);
        $qaWrapperFieldset->shouldReceive('add')
            ->with($this->fieldset)
            ->once();

        $this->form = m::mock(Form::class);
        $this->form->shouldReceive('get')
            ->with('qa')
            ->andReturn($qaWrapperFieldset);

        $fieldsetFactory = m::mock(FieldsetFactory::class);
        $fieldsetFactory->shouldReceive('create')
            ->with(self::FIELDSET_NAME, self::ELEMENT_TYPE)
            ->once()
            ->andReturn($this->fieldset);

        $fieldsetPopulator = m::mock(FieldsetPopulatorInterface::class);
        $fieldsetPopulator->shouldReceive('populate')
            ->with($this->form, $this->fieldset, self::ELEMENT_OPTIONS)
            ->once()
            ->globally()
            ->ordered();

        $fieldsetModifier = m::mock(FieldsetModifier::class);
        $fieldsetModifier->shouldReceive('modify')
            ->with($this->fieldset)
            ->once()
            ->globally()
            ->ordered();

        $fieldsetPopulatorProvider = m::mock(FieldsetPopulatorProvider::class);
        $fieldsetPopulatorProvider->shouldReceive('get')
            ->with(self::ELEMENT_TYPE)
            ->once()
            ->andReturn($fieldsetPopulator);

        $this->sut = new FieldsetAdder($fieldsetPopulatorProvider, $fieldsetFactory, $fieldsetModifier);
    }

    /**
     * @dataProvider dpEnabled
     */
    public function testAddSelfserve($enabled, $expectedDataEnabledAttribute): void
    {
        $options = [
            'fieldsetName' => self::FIELDSET_NAME,
            'shortName' => self::SHORT_NAME,
            'type' => self::ELEMENT_TYPE,
            'element' => self::ELEMENT_OPTIONS,
            'enabled' => $enabled,
        ];

        $this->fieldset->shouldReceive('setAttribute')
            ->with('data-enabled', $expectedDataEnabledAttribute)
            ->once();

        $this->sut->add($this->form, $options, UsageContext::CONTEXT_SELFSERVE);
    }

    /**
     * @dataProvider dpEnabled
     */
    public function testAddInternal($enabled, $expectedDataEnabledAttribute): void
    {
        $options = [
            'fieldsetName' => self::FIELDSET_NAME,
            'shortName' => self::SHORT_NAME,
            'type' => self::ELEMENT_TYPE,
            'element' => self::ELEMENT_OPTIONS,
            'enabled' => $enabled
        ];

        $this->fieldset->shouldReceive('setAttribute')
            ->with('data-enabled', $expectedDataEnabledAttribute)
            ->once();

        $this->fieldset->shouldReceive('setLabel')
            ->with(self::SHORT_NAME)
            ->once()
            ->globally()
            ->ordered();

        $this->fieldset->shouldReceive('setLabelAttributes')
            ->with([])
            ->once()
            ->globally()
            ->ordered();

        $this->sut->add($this->form, $options, UsageContext::CONTEXT_INTERNAL);
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{true, 'true'}, list{false, 'false'}}
     */
    public function dpEnabled(): array
    {
        return [
            [true, 'true'],
            [false, 'false'],
        ];
    }
}
