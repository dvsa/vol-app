<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\QaRadio;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Validator\InArray;

/**
 * @covers \Common\Form\Elements\InputFilters\QaRadio
 */
class QaRadioTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $name = 'qaRadioName';
        $notSelectedMessage = 'not selected message';
        $valueOptions = [
            'option1' => 'value1',
            'option2' => 'value2'
        ];

        $qaRadio = new QaRadio($name);
        $qaRadio->setOption('not_selected_message', $notSelectedMessage);
        $qaRadio->setValueOptions($valueOptions);

        $expected = [
            'name' => $name,
            'continue_if_empty' => true,
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['option1', 'option2'],
                        'strict' => true,
                        'messages' => [
                            InArray::NOT_IN_ARRAY => $notSelectedMessage
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $qaRadio->getInputSpecification());
    }
}
