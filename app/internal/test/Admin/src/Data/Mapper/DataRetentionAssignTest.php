<?php

namespace AdminTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Data\Mapper\DataRetentionAssign as Sut;
use Laminas\Form\FormInterface;

/**
 * Data Retention Assign Test
 */
class DataRetentionAssignTest extends MockeryTestCase
{
    public function testMapFromForm()
    {
        $assignedTo = 'assigned to';
        $ids = 'ids';

        $input = [
            'fields' => [
                'assignedTo' => $assignedTo
            ],
            'ids' => $ids
        ];

        $expected = [
            'user' => $assignedTo,
            'ids' => $ids
        ];

        $this->assertEquals($expected, Sut::mapFromForm($input));
    }

    public function testMapFromResult()
    {
        $inputData = ['inputData'];
        $this->assertEquals($inputData, Sut::mapFromResult($inputData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
