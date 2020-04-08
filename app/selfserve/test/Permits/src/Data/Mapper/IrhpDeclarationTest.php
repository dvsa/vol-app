<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Form;
use Mockery as m;
use Permits\Data\Mapper\IrhpDeclaration;
use Zend\Form\Fieldset;

class IrhpDeclarationTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public $sut;

    public function setUp()
    {
        $this->sut = new IrhpDeclaration();
    }

    public function testMapForFormOptionsBilateral()
    {
        $inputData = [
            'type' => 4
        ];

        $mockFieldSet = m::mock(Fieldset::class);
        $mockForm = m::mock(Form::class);
        $mockField = m::mock(SingleCheckbox::class);

        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockFieldSet);
        $mockFieldSet->shouldReceive('get')->once()->with('declaration')->andReturn($mockField);
        $mockField->shouldReceive('setLabel')->once()->with(IrhpDeclaration::BILATERAL_DECLARATION_LABEL);

        $this->assertEquals(
            $inputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testMapForFormOptionsOther()
    {
        $inputData = [
            'type' => 2
        ];

        $mockForm = m::mock(Form::class);
        $mockForm->shouldNotReceive('get');

        $this->assertEquals(
            $inputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }
}
