<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\DataRetentionRule as Sut;
use Laminas\Form\FormInterface;
use Mockery as m;

/**
 * Class DataRetentionRuleTest
 */
class DataRetentionRuleTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'id' => 1,
            'description' => 'Lorem ipsum',
            'retentionPeriod' => 60,
            'maxDataSet' => 1000,
            'isEnabled' => true,
            'actionType' => ['id' => 'Automate']
        ];

        $mappedData = [
            'ruleDetails' => [
                'id' => 1,
                'description' => 'Lorem ipsum',
                'retentionPeriod' => 60,
                'maxDataSet' => 1000,
                'isEnabled' => 'Y',
                'actionType' => 'Automate'
            ]
        ];

        $this->assertEquals($mappedData, Sut::mapFromResult($data));

        $data = [
            'id' => 1,
            'description' => 'Lorem ipsum',
            'retentionPeriod' => 60,
            'maxDataSet' => 1000,
            'isEnabled' => false,
            'actionType' => ['id' => 'Automate']
        ];

        $mappedData = [
            'ruleDetails' => [
                'id' => 1,
                'description' => 'Lorem ipsum',
                'retentionPeriod' => 60,
                'maxDataSet' => 1000,
                'isEnabled' => 'N',
                'actionType' => 'Automate'
            ]
        ];
        $this->assertEquals($mappedData, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {

        $formData = [
            'ruleDetails' => [
                'id' => 1,
                'description' => 'Lorem ipsum',
                'retentionPeriod' => 60,
                'maxDataSet' => 1000,
                'isEnabled' => 'Y',
                'actionType' => 'Automate'
            ]
        ];

        $mappedData = [
            'id' => 1,
            'description' => 'Lorem ipsum',
            'retentionPeriod' => 60,
            'maxDataSet' => 1000,
            'isEnabled' => true,
            'actionType' => 'Automate'
        ];
        $this->assertEquals($mappedData, Sut::mapFromForm($formData));

        $formData = [
            'ruleDetails' => [
                'id' => 1,
                'description' => 'Lorem ipsum',
                'retentionPeriod' => 60,
                'maxDataSet' => 1000,
                'isEnabled' => 'N',
                'actionType' => 'Automate'
            ]
        ];

        $mappedData = [
            'id' => 1,
            'description' => 'Lorem ipsum',
            'retentionPeriod' => 60,
            'maxDataSet' => 1000,
            'isEnabled' => false,
            'actionType' => 'Automate'
        ];

        $this->assertEquals($mappedData, Sut::mapFromForm($formData));
    }

    public function testMapFromErrors()
    {
        /** @var FormInterface $form  */
        $form = m::mock(FormInterface::class);
        $this->assertEquals(['FOO'], Sut::mapFromErrors($form, ['FOO']));
    }
}
