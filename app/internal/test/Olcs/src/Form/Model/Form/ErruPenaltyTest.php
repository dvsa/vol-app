<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\Data\Object as F;

/**
 * Class ErruPenaltyTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class ErruPenaltyTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\ErruPenalty';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'siPenaltyType'],
                ['101' => 'Penalty type 1']
            ],
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'siPenaltyType']),
                new F\Value(F\Value::VALID, '101'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'startDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'13', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'aaa']),
                new F\Value(F\Value::INVALID, ['day'=>'32', 'month'=>'09', 'year'=>'2013'])
            ),
            new F\Test(
                new F\Stack(['fields', 'endDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'13', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'aaa']),
                new F\Value(F\Value::INVALID, ['day'=>'32', 'month'=>'09', 'year'=>'2013'])
            ),
            new F\Test(
                new F\Stack(['fields', 'imposed']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'reasonNotImposed']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 501, '+'))
            ),
        ];
    }
}
