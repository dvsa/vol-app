<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\ValidatorsAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\ValidatorChain;

/**
 * ValidatorsAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ValidatorsAdderTest extends MockeryTestCase
{
    private $form;

    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->form = m::mock(Form::class);

        $this->sut = new ValidatorsAdder();
    }

    public function testAdd(): void
    {
        $fieldsetName = 'fields123';

        $betweenValidatorRule = 'Between';
        $betweenValidatorParams = [
            'min' => 5,
            'max' => 10
        ];

        $greaterThanValidatorRule = 'GreaterThan';
        $greaterThanValidatorParams = [
            'min' => 40,
            'inclusive' => true
        ];

        $options = [
            'fieldsetName' => $fieldsetName,
            'validators' => [
                [
                    'rule' => $betweenValidatorRule,
                    'params' => $betweenValidatorParams
                ],
                [
                    'rule' => $greaterThanValidatorRule,
                    'params' => $greaterThanValidatorParams
                ]
            ]
        ];

        $qaElementValidatorChain = m::mock(ValidatorChain::class);
        $qaElementValidatorChain->shouldReceive('attachByName')
            ->with($betweenValidatorRule, $betweenValidatorParams)
            ->ordered()
            ->once();
        $qaElementValidatorChain->shouldReceive('attachByName')
            ->with($greaterThanValidatorRule, $greaterThanValidatorParams)
            ->ordered()
            ->once();

        $qaElementInput = m::mock(ElementInterface::class);
        $qaElementInput->shouldReceive('setContinueIfEmpty')
            ->with(true)
            ->once();
        $qaElementInput->shouldReceive('getValidatorChain')
            ->andReturn($qaElementValidatorChain);

        $fieldsetInputFilter = m::mock(InputFilterInterface::class);
        $fieldsetInputFilter->shouldReceive('get')
            ->with('qaElement')
            ->andReturn($qaElementInput);

        $qaFieldsetInputFilter = m::mock(InputFilterInterface::class);
        $qaFieldsetInputFilter->shouldReceive('get')
            ->with($fieldsetName)
            ->andReturn($fieldsetInputFilter);

        $formInputFilter = m::mock(InputFilterInterface::class);
        $formInputFilter->shouldReceive('get')
            ->with('qa')
            ->andReturn($qaFieldsetInputFilter);

        $this->form->shouldReceive('getInputFilter')
            ->withNoArgs()
            ->andReturn($formInputFilter);

        m::mock(InputInterface::class);

        $this->sut->add($this->form, $options);
    }

    public function testAddWithNoValidators(): void
    {
        $options = [
            'validators' => []
        ];

        $this->form->shouldReceive('getInputFilter')
            ->never();

        $this->sut->add($this->form, $options);
    }
}
