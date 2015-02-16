<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class PublicInquiryAgreedAndLegislationTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PublicInquiryAgreedAndLegislationTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\PublicInquiryAgreedAndLegislation';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'agreedByTc'],
                ['tc1' => 'TC 1', 'tc2' => 'TC 2']
            ],
            [
                ['fields', 'agreedByTcRole'],
                ['tcrole1' => 'TC 1', 'tcrole2' => 'TC 2']
            ],
            [
                ['fields', 'piTypes'],
                ['pi_t_tm_only' => 'TM only', 'type2' => 'Type 2', 'type3' => 'Type 3']
            ],
            [
                ['fields', 'reasons'],
                ['reason1' => 'Reason 1', 'reason2' => 'Reason 2', 'reason3' => 'Reason 3']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'agreedDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'2500'])
            ),
            new F\Test(
                new F\Stack(['fields', 'agreedByTc']),
                new F\Value(F\Value::VALID, 'tc1'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'agreedByTcRole']),
                new F\Value(F\Value::VALID, 'tcrole1'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'piTypes']),
                new F\Value(F\Value::VALID, ['pi_t_tm_only']),
                new F\Value(F\Value::VALID, ['type2', 'type3']),
                new F\Value(F\Value::INVALID, ['pi_t_tm_only', 'type2']),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'reasons']),
                new F\Value(F\Value::VALID, ['reason1']),
                new F\Value(F\Value::VALID, ['reason2', 'reason3']),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'comment']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'sdfjksdfjhksjdhksdjhfksdjfh'),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            )
        ];
    }
}
