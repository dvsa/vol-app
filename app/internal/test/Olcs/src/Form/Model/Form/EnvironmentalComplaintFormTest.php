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
                ['ecst_open' => 'Open', 'ecst_closed' => 'Closed']
            ],
            [
                ['fields', 'ocComplaints'],
                ['1' => 'OC 1', '2' => 'OC 2']
            ],
            [
                ['address', 'countryCode'],
                ['uk' => 'United Kingdom']
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
                new F\Stack(['address', 'addressLine1']),
                new F\Value(F\Value::VALID, 'anystreet'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['address', 'addressLine2']),
                new F\Value(F\Value::VALID, 'anystreet'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['address', 'addressLine3']),
                new F\Value(F\Value::VALID, 'anystreet'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['address', 'addressLine4']),
                new F\Value(F\Value::VALID, 'anystreet'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['address', 'town']),
                new F\Value(F\Value::VALID, 'Leeds'),
                new F\Value(F\Value::INVALID, [])
            ),
            new F\Test(
                new F\Stack(['address', 'countryCode']),
                new F\Value(F\Value::VALID, 'uk'),
                new F\Value(F\Value::INVALID, 'as')
            ),
            new F\Test(
                new F\Stack(['address', 'postcode']),
                new F\Value(F\Value::VALID, 'AB1 2CD'),
                new F\Value(F\Value::INVALID, null)
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
                new F\Value(F\Value::VALID, 'ecst_open'),
                new F\Value(F\Value::VALID, 'ecst_closed'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, '')
            ),
            new F\Test(
                new F\Stack(['fields', 'ocComplaints']),
                new F\Value(F\Value::VALID, [1]),
                new F\Value(F\Value::VALID, [1,2]),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, '')
            )
        ];
    }
}
