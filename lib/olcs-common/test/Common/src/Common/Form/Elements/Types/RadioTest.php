<?php

namespace CommonTest\Form\Elements\Types;

use Common\View\Helper\UniqidGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\Elements\Types\Radio;
use Mockery as m;

/**
 * RadioTest
 */
class RadioTest extends MockeryTestCase
{
    public function testSetName(): void
    {
        $sut = new Radio();
        $sut->setName('FOO');
        $this->assertSame('FOO', $sut->getAttribute('id'));
    }

    public function testSetNameIdAlreadySet(): void
    {
        $sut = new Radio();
        $sut->setAttribute('id', 'NOT-FOO');
        $sut->setName('FOO');
        $this->assertSame('NOT-FOO', $sut->getAttribute('id'));
    }

    public function testSetValueOptions(): void
    {
        $idGenerator = m::mock(UniqidGenerator::class);
        $idGenerator->shouldReceive('generateId')->twice()->andReturn('generated_id');
        $sut = new Radio(null, [], $idGenerator);

        $sut->setValueOptions(
            [
                'A' => 'aaa',
                'B' => 'bbb',
                'C' => [
                    'label' => 'ccc',
                    'value' => 'C_value',
                    'attributes' => [
                        'id' => 'custom_id',
                        'class' => 'custom_class'
                    ]
                ]
            ]
        );

        $valueOptions = $sut->getValueOptions();

        $expected = [
            'A' => [
                'label' => 'aaa',
                'value' => 'A',
                'attributes' => [
                    'id' => 'generated_id_A',
                    'data-show-element' => '#generated_id_A_content',
                ],
            ],
            'B' => [
                'label' => 'bbb',
                'value' => 'B',
                'attributes' => [
                    'id' => 'generated_id_B',
                    'data-show-element' => '#generated_id_B_content',
                ],
            ],
            'C' => [
                'label' => 'ccc',
                'value' => 'C_value',
                'attributes' => [
                    'id' => 'custom_id',
                    'data-show-element' => '#custom_id_content',
                    'class' => 'custom_class',
                ],
            ],
        ];

        $this->assertSame($expected, $valueOptions);
    }
}
