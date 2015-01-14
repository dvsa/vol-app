<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class EnvironmentalComplaintFormTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class EnvironmentalComplaintFormTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\EnvironmentalComplaint';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'status'],
                ['cst_open' => 'Open', 'cst_closed' => 'Closed']
            ],
            [
                ['fields', 'ocComplaints'],
                ['1' => 'OC 1', '2' => 'OC 2']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'complaintDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'2500']),
                new F\Value(F\Value::INVALID, ['day'=>'31', 'month'=>'02', 'year'=>'2015'])
            ),
            new F\Test(
                new F\Stack(['fields', 'complainantForename']),
                new F\Value(F\Value::VALID, 'John'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'a'),
                new F\Value(F\Value::INVALID, 'This is longer than the max123456789')
            ),
            new F\Test(
                new F\Stack(['fields', 'complainantFamilyName']),
                new F\Value(F\Value::VALID, 'Smith'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'a'),
                new F\Value(F\Value::INVALID, 'This is longer than the max123456789')
            ),
            new F\Test(
                new F\Stack(['fields', 'description']),
                new F\Value(F\Value::VALID, 'A description between 5-4000 chars'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::VALID, str_pad('', 4000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'status']),
                new F\Value(F\Value::VALID, 'cst_open'),
                new F\Value(F\Value::VALID, 'cst_closed'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, '')
            )
        ];
    }
}
