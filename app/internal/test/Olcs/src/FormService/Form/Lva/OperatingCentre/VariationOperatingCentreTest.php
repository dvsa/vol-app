<?php

/**
 * Variation Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\OperatingCentre\VariationOperatingCentre;
use Zend\Validator\Identical as ValidatorIdentical;

/**
 * Variation Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new VariationOperatingCentre();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);

        $this->formHelper
            ->shouldReceive('removeValidator')
            ->with($form, 'data->permission', ValidatorIdentical::class)
            ->once()
            ->shouldReceive('removeValidator')
            ->with($form, 'data->sufficientParking', ValidatorIdentical::class)
            ->once();
        $form->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('address')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('postcode')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setRequired')
                                    ->with(false)
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->once(3);

        $params = [
            'isPsv' => false,
            'canAddAnother' => true,
            'canUpdateAddress' => true,
            'wouldIncreaseRequireAdditionalAdvertisement' => false
        ];
        $this->sut->alterForm($form, $params);
    }
}
