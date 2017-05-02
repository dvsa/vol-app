<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;
use Zend\Validator\Identical as ValidatorIdentical;
use Common\RefData;

/**
 * Lva Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LvaOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new LvaOperatingCentre();
        $this->sut->setFormHelper($this->formHelper);
    }

    /**
     * @dataProvider alterFormProvider
     */
    public function testAlterForm($appliedVia)
    {
        $originalValueOptions = [
            RefData::AD_UPLOAD_LATER => 'Upload later',
            'Foo' => 'Bar'
        ];
        $alteredValueOptions = ['Foo' => 'Bar'];
        $form = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setOption')
                            ->with('shouldEscapeMessages', false)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->formHelper
            ->shouldReceive('removeValidator')
            ->with($form, 'data->permission', ValidatorIdentical::class)
            ->once()
            ->shouldReceive('removeValidator')
            ->with($form, 'data->sufficientParking', ValidatorIdentical::class)
            ->once()
            ->shouldReceive('removeValidator')
            ->with($form, 'advertisements->uploadedFileCount', \Common\Validator\ValidateIf::class)
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
            ->once()
            ->shouldReceive('get')
            ->with('advertisements')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('adPlaced')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValueOptions')
                    ->andReturn($originalValueOptions)
                    ->once()
                    ->shouldReceive('setValueOptions')
                    ->with($alteredValueOptions)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $params = [
            'isPsv' => false,
            'canAddAnother' => true,
            'canUpdateAddress' => true,
            'wouldIncreaseRequireAdditionalAdvertisement' => false,
            'appliedVia' => $appliedVia,
        ];
        $this->sut->alterForm($form, $params);
    }

    public function alterFormProvider()
    {
        return [
            [RefData::APPLIED_VIA_POST],
            [['id' => RefData::APPLIED_VIA_POST]]
        ];
    }
}
