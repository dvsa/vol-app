<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\DocumentRelink as Sut;
use Laminas\Form\Form;

/**
 * DocumentRelink Mapper Test
 */
class DocumentRelinkTest extends MockeryTestCase
{
    public function testMapFromForm()
    {
        $data = [
            'document-relink-details' => [
                'type' => 'application',
                'targetId' => 1,
                'ids' => '1,2,3'
            ]
        ];

        $expected = [
            'type' => 'application',
            'targetId' => 1,
            'ids' => [1, 2, 3]
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors['messages'] = [
            'type' => ['error1'],
            'targetId' => ['error2'],
            'general' => ['error3'],
        ];

        $expected = [
            'general' => ['error3']
        ];

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
