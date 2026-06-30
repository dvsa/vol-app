<?php

namespace CommonTest\Form;

use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilter;
use Common\Form\InsufficientFinancesForm;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class InsufficientFinancesFormTest extends TestCase
{
    /**
     * @var InsufficientFinancesForm
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new InsufficientFinancesForm();

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestIsValid
     */
    public function testIsValid(
        $yesNoValue,
        $radioValue,
        $expectRadioRequired,
        $expectFileCountRequired,
        $expectYesNoSetMessage
    ): void {
        $yesNoInput = m::mock(ElementInterface::class);
        if ($expectYesNoSetMessage) {
            $yesNoInput->shouldReceive('setErrorMessage')->with('continuations.insufficient-finances.no')->once();
        }

        $fileCountInput = m::mock(ElementInterface::class);
        $fileCountInput->shouldReceive('setRequired')->with($expectFileCountRequired)->once();
        $fileCountInput->shouldReceive('setErrorMessage')->with('continuations.insufficient-finances.upload-files')
            ->once();

        $radioInput = m::mock(ElementInterface::class);
        $radioInput->shouldReceive('setRequired')->with($expectRadioRequired)->once();

        $this->initForm($radioValue, $yesNoValue, $yesNoInput, $fileCountInput, $radioInput);
        $this->sut->isValid();
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'X', '', false, false, false}, list{'N', '', false, false, true}, list{'Y', '', true, false, false}, list{'Y', 'X', true, false, false}, list{'Y', 'upload', true, true, false}, list{'Y', 'send', true, false, false}}
     */
    public function dataProviderTestIsValid(): array
    {
        return [
            ['X', '', false, false, false],
            ['N', '', false, false, true],
            ['Y', '', true, false, false],
            ['Y', 'X', true, false, false],
            ['Y', 'upload', true, true, false],
            ['Y', 'send', true, false, false],
        ];
    }

    /**
     * @param m\LegacyMockInterface&m\MockInterface&ElementInterface $yesNoInput
     * @param m\LegacyMockInterface&m\MockInterface&ElementInterface $fileCountInput
     * @param m\LegacyMockInterface&m\MockInterface&ElementInterface $radioInput
     */
    private function initForm($radioValue, $yesNoValue, ElementInterface $yesNoInput, ElementInterface $fileCountInput, ElementInterface $radioInput): void
    {
        $insufficientFinancesFieldset = m::mock(Fieldset::class)->makePartial();
        $insufficientFinancesFieldset->setName('insufficientFinances');
        $insufficientFinancesFieldset->shouldReceive('get')->with('yesContent')->once()->andReturn(
            m::mock(ElementInterface::class)->shouldReceive('get')->with('radio')->once()->andReturn(
                m::mock(ElementInterface::class)->shouldReceive('getValue')->with()->once()->andReturn($radioValue)->getMock()
            )->getMock()
        );
        $insufficientFinancesFieldset->shouldReceive('get')->with('yesNo')->twice()->andReturn(
            m::mock(ElementInterface::class)->shouldReceive('getValue')->with()->twice()->andReturn($yesNoValue)->getMock()
        );

        $this->sut->setData(['x' => 1]);
        $this->sut->setUseInputFilterDefaults(false);
        $this->sut->add($insufficientFinancesFieldset);

        $uploadContentInput = m::mock(ElementInterface::class);
        $uploadContentInput->shouldReceive('get')->with('fileCount')->andReturn($fileCountInput);

        $yesContentInput = m::mock(ElementInterface::class);
        $yesContentInput->shouldReceive('get')->with('radio')->andReturn($radioInput);
        $yesContentInput->shouldReceive('get')->with('uploadContent')->andReturn($uploadContentInput);

        $insufficientFinancesInput = m::mock(ElementInterface::class);
        $insufficientFinancesInput->shouldReceive('get')->with('yesContent')->andReturn($yesContentInput);
        $insufficientFinancesInput->shouldReceive('get')->with('yesNo')->andReturn($yesNoInput);

        $inputFilter = m::mock(InputFilter::class)->makePartial();
        $inputFilter->shouldReceive('get')->with('insufficientFinances')->andReturn($insufficientFinancesInput);

        $this->sut->setInputFilter($inputFilter);
    }
}
