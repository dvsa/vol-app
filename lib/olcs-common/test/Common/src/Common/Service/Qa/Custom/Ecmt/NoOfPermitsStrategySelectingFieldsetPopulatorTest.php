<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\NoOfPermitsStrategySelectingFieldsetPopulator;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * NoOfPermitsStrategySelectingFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsStrategySelectingFieldsetPopulatorTest extends MockeryTestCase
{
    private $form;

    private $fieldset;

    private $singleEmissionsCategoryFieldsetPopulator;

    private $multipleEmissionsCategoryFieldsetPopulator;

    private $noOfPermitsStrategySelectingFieldsetPopulator;

    #[\Override]
    protected function setUp(): void
    {
        $this->form = m::mock(Form::class);

        $this->fieldset = m::mock(Fieldset::class);

        $this->singleEmissionsCategoryFieldsetPopulator = m::mock(FieldsetPopulatorInterface::class);

        $this->multipleEmissionsCategoryFieldsetPopulator = m::mock(FieldsetPopulatorInterface::class);

        $this->noOfPermitsStrategySelectingFieldsetPopulator = new NoOfPermitsStrategySelectingFieldsetPopulator(
            $this->singleEmissionsCategoryFieldsetPopulator,
            $this->multipleEmissionsCategoryFieldsetPopulator
        );
    }

    public function testPopulateSingleEmissionsCategory(): void
    {
        $options = [
            'emissionsCategories' => [
                [
                    'emissionsCategory1Key1' => 'emissionsCategory1Value1',
                    'emissionsCategory1Key2' => 'emissionsCategory1Value2',
                ]
            ]
        ];

        $this->singleEmissionsCategoryFieldsetPopulator->shouldReceive('populate')
            ->with($this->form, $this->fieldset, $options)
            ->once();

        $this->noOfPermitsStrategySelectingFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    public function testMultipleEmissionsCategories(): void
    {
        $options = [
            'emissionsCategories' => [
                [
                    'emissionsCategory1Key1' => 'emissionsCategory1Value1',
                    'emissionsCategory1Key2' => 'emissionsCategory1Value2',
                ],
                [
                    'emissionsCategory2Key1' => 'emissionsCategory2Value1',
                    'emissionsCategory2Key2' => 'emissionsCategory2Value2',
                ]
            ]
        ];

        $this->multipleEmissionsCategoryFieldsetPopulator->shouldReceive('populate')
            ->with($this->form, $this->fieldset, $options)
            ->once();

        $this->noOfPermitsStrategySelectingFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }
}
