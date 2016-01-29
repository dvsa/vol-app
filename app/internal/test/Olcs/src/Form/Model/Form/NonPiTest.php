<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class NonPiTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\NonPi';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'hearingType'],
                ['1' => 'HT 1', '2' => 'HT 2']
            ],
            [
                ['fields', 'venue'],
                ['23' => 'VENUE 1', '24' => 'VENUE 2']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'hearingType']),
                new F\Value(F\Value::VALID, '1'),
                new F\Value(F\Value::VALID, '2'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'hearingDate']),
                new F\Value(F\Value::VALID, '2014-01-01'),
                new F\Value(F\Value::VALID, '2014-01-01 11:00:00'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'venue']),
                new F\Value(F\Value::VALID, '23'),
                new F\Value(F\Value::VALID, '24'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'venueOther']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'venue']), 'other')),
                new F\Value(
                    F\Value::VALID,
                    str_pad('1', 250, '_'),
                    new F\Context(new F\Stack(['fields', 'venue']), 'other')
                ),
                new F\Value(
                    F\Value::INVALID,
                    str_pad('1', 300, '_'),
                    new F\Context(new F\Stack(['fields', 'venue']), 'other')
                ),
                new F\Value(F\Value::VALID, null, new F\Context(new F\Stack(['fields', 'venue']), 'other'))
            ),
            new F\Test(
                new F\Stack(['fields', 'witnessCount']),
                new F\Value(F\Value::VALID, '20'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, ""),
                new F\Value(F\Value::INVALID, 'ABC')
            ),
            new F\Test(
                new F\Stack(['fields', 'agreedByTcDate']),
                new F\Value(F\Value::VALID, ['day'=>'28', 'month'=>'02', 'year'=>'2014']),
                new F\Value(F\Value::VALID, ['day'=>'28', 'month'=>'02', 'year'=>'2014']),
                new F\Value(F\Value::INVALID, ['day'=>'29', 'month'=>'02', 'year'=>'2014']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'29', 'month'=>'02', 'year'=>'2016']
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'30', 'month'=>'02', 'year'=>'2014']
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'13', 'year'=>'2014']
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'presidingStaffName']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, "John smith"),
                new F\Value(F\Value::VALID, str_pad('1', 255, '_')),
                new F\Value(F\Value::INVALID, str_pad('1', 256, '_'))
            )
        ];
    }
}
