<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Common\Data\Mapper\Lva\OperatingCentre as OperatingCentreMapper;
use Common\RefData;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\BaseInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Identical as ValidatorIdentical;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;

/**
 * Lva Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LvaOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new LvaOperatingCentre($this->formHelper);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormProvider')]
    public function testAlterForm(mixed $appliedVia): void
    {
        $form = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setOption')
                            ->with('shouldEscapeMessages', false)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('advertisements')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('radio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                        ->shouldReceive('getValueOptions')
                        ->andReturn(['foo' => 'bar', OperatingCentreMapper::VALUE_OPTION_AD_UPLOAD_LATER => 'cake'])
                        ->twice()
                        ->shouldReceive('setValueOptions')
                        ->with(['foo' => 'bar'])
                        ->once()
                        ->shouldReceive('setValueOptions')
                        ->with([
                            'foo' => 'bar',
                            'adPlacedLater' => 'cake',
                            'adSendByPost' => 'No (operator to post)'
                        ])
                        ->once()
                        ->getMock()
                    )
                    ->twice()
                    ->shouldReceive('setLabel')
                    ->with('application_operating-centres_authorisation-sub-action.advertisements.adPlaced')
                    ->once()
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('guidance')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setValue')
                            ->with('lva-operating-centre-newspaper-advert')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('permission')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setLabel')
                            ->with('')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $this->formHelper
            ->shouldReceive('removeValidator')
            ->with($form, 'data->permission->permission', ValidatorIdentical::class)
            ->once()
            ->shouldReceive('removeValidator')
            ->with($form, 'advertisements->uploadedFileCount', \Common\Validator\ValidateIf::class)
            ->once()
            ->shouldReceive('remove')
            ->with($form, 'advertisements->adPlacedLaterContent')
            ->once()
            ->getMock();

        $addressInputFilter = m::mock(InputFilterInterface::class);

        $addressInputFilter
                ->shouldReceive('get')
                ->with('postcode')
                ->andReturn(
                    m::mock(InputFilterInterface::class)
                        ->shouldReceive('setRequired')
                        ->with(false)
                        ->once()
                        ->getMock()
                )
                ->once();

        $postcodeSearchInputFilter = $this->createMock(BaseInputFilter::class);
        $postcodeSearchInputFilter->method('getInputs')->willReturn([]);

        $addressInputFilter
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($postcodeSearchInputFilter);

        $form->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock(InputFilterInterface::class)
                    ->shouldReceive('get')
                    ->with('address')
                    ->andReturn($addressInputFilter)
                    ->getMock()
            )
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

    public static function alterFormProvider(): array
    {
        return [
            [RefData::APPLIED_VIA_POST],
            [['id' => RefData::APPLIED_VIA_POST]]
        ];
    }
}
