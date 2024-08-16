<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\DataRetentionExport as Sut;
use Laminas\Form\FormInterface;
use Mockery as m;

/**
 * Class DataRetentionExportTest
 */
class DataRetentionExportTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $this->assertEquals(['FOO'], Sut::mapFromResult(['FOO']));
    }

    public function testMapFromForm()
    {
        $formData = [
            'exportOptions' => [
                'rule' => 12,
                'startDate' => 'START_DATE',
                'endDate' => 'END_DATE',
                'foo' => 'bar',
            ]
        ];

        $this->assertEquals(
            [
                'dataRetentionRuleId' => 12,
                'startDate' => 'START_DATE',
                'endDate' => 'END_DATE',
            ],
            Sut::mapFromForm($formData)
        );
    }

    public function testMapFromErrors()
    {
        $form = m::mock(FormInterface::class);
        $this->assertEquals(['FOO'], Sut::mapFromErrors($form, ['FOO']));
    }
}
