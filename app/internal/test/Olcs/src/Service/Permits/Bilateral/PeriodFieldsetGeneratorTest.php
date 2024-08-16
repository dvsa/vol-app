<?php

/**
 * PeriodFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Olcs\Service\Permits\Bilateral\FieldsetPopulatorInterface;
use Olcs\Service\Permits\Bilateral\PeriodFieldsetGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use RuntimeException;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\Fieldset;

/**
 * PeriodFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class PeriodFieldsetGeneratorTest extends TestCase
{
    public const FIELDSET_POPULATOR_TYPE_2_NAME = 'populatorType2';

    public const PERIOD_ID = 45;

    public const PERIOD_FIELDS = [
        'fieldData1',
        'fieldData2'
    ];

    public const PERIOD_DATA = [
        'id' => self::PERIOD_ID,
        'fields' => self::PERIOD_FIELDS,
    ];

    private $formFactory;

    private $periodFieldsetGenerator;

    private $fieldsetPopulatorType2;

    public function setUp(): void
    {
        $this->formFactory = m::mock(FormFactory::class);

        $this->periodFieldsetGenerator = new PeriodFieldsetGenerator($this->formFactory);

        $this->periodFieldsetGenerator->registerFieldsetPopulator(
            'populatorType1',
            m::mock(FieldsetPopulatorInterface::class)
        );

        $this->fieldsetPopulatorType2 = m::mock(FieldsetPopulatorInterface::class);

        $this->periodFieldsetGenerator->registerFieldsetPopulator(
            self::FIELDSET_POPULATOR_TYPE_2_NAME,
            $this->fieldsetPopulatorType2
        );

        $this->periodFieldsetGenerator->registerFieldsetPopulator(
            'populatorType3',
            m::mock(FieldsetPopulatorInterface::class)
        );
    }

    public function testGenerate()
    {
        $periodFieldset = m::mock(Fieldset::class);

        $periodFieldsetParams = [
            'type' => Fieldset::class,
            'name' => 'period45',
            'attributes' => [
                'id' => 'period45',
                'data-role' => 'period',
            ]
        ];

        $this->formFactory->shouldReceive('create')
            ->with($periodFieldsetParams)
            ->andReturn($periodFieldset);

        $this->fieldsetPopulatorType2->shouldReceive('populate')
            ->with($periodFieldset, self::PERIOD_FIELDS)
            ->once();

        $this->assertSame(
            $periodFieldset,
            $this->periodFieldsetGenerator->generate(self::PERIOD_DATA, self::FIELDSET_POPULATOR_TYPE_2_NAME)
        );
    }

    public function testGenerateExceptionPopulatorNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fieldset populator not found for type unknownType');

        $this->periodFieldsetGenerator->generate(self::PERIOD_DATA, 'unknownType');
    }
}
