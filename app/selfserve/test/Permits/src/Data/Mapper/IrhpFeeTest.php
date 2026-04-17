<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Permits\Data\Mapper\IrhpFee;
use Laminas\Form\Element\Submit;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * IrhpFeeTest
 */
class IrhpFeeTest extends TestCase
{
    private $irhpFee;

    private $form;

    public function setUp(): void
    {
        $this->irhpFee = new IrhpFee();

        $this->form = m::mock(Form::class);
    }

    public function testMapForFormOptionsBilateralWithoutFees(): void
    {
        $data = [
            'application' => [
                'isBilateral' => true,
                'hasOutstandingFees' => false
            ]
        ];

        $submit = m::mock(Submit::class);
        $submit->shouldReceive('setValue')
            ->with(IrhpFee::SUBMIT_APPLICATION_CAPTION)
            ->once();

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('SubmitButton')
            ->andReturn($submit);

        $this->form->shouldReceive('get')
            ->with('Submit')
            ->andReturn($fieldset);

        $this->irhpFee->mapForFormOptions($data, $this->form);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpMapForFormOptionsOther')]
    public function testMapForFormOptionsOther(bool $isBilateral, bool $hasOutstandingFees): void
    {
        $data = [
            'application' => [
                'isBilateral' => $isBilateral,
                'hasOutstandingFees' => $hasOutstandingFees
            ]
        ];

        $this->form->shouldReceive('get')
            ->never();

        $this->irhpFee->mapForFormOptions($data, $this->form);
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{false, false}, list{false, true}, list{true, true}}
     */
    public static function dpMapForFormOptionsOther(): array
    {
        return [
            [false, false],
            [false, true],
            [true, true],
        ];
    }
}
