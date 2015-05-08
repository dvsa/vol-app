<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class PublicInquiryHearingTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PublicInquiryHearingTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\PublicInquiryHearing';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'piVenue'],
                ['1' => 'Venue 1', 'other' => 'Other']
            ],
            [
                ['fields', 'presidedByRole'],
                ['tc_r_tc' => 'Traffic Commissioner']
            ],
            [
                ['fields', 'presidingTc'],
                ['1' => 'PresidingTc 1', '2' => 'PresidingTc 2', '3' => 'PresidingTc 3']
            ],
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'piVenue']),
                new F\Value(F\Value::VALID, 1),
                new F\Value(F\Value::VALID, 'other'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'piVenueOther']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'piVenue']), 1)),
                new F\Value(F\Value::VALID, 'abcdefgh', new F\Context(new F\Stack(['fields', 'piVenue']), 'other')),
                new F\Value(F\Value::INVALID, '', new F\Context(new F\Stack(['fields', 'piVenue']), 'other')),
                new F\Value(
                    F\Value::INVALID,
                    str_pad('', 256, '+'),
                    new F\Context(new F\Stack(['fields', 'piVenue']), 'other')
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'hearingDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'10', 'year'=>'2016', 'hour'=>'08', 'minute'=>'45']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'10', 'year'=>'2016', 'hour'=>'22', 'minute'=>'30']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'02', 'year'=>'2014', 'hour'=>'22', 'minute'=>'61']
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'02', 'year'=>'2014', 'hour'=>'25', 'minute'=>'41']
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'30', 'month'=>'02', 'year'=>'2014', 'hour'=>'22', 'minute'=>'41']
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'13', 'year'=>'2014', 'hour'=>'22', 'minute'=>'41']
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'presidingTc']),
                new F\Value(F\Value::VALID, 1),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'presidedByRole']),
                new F\Value(F\Value::VALID, 'tc_r_tc'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'witnesses']),
                new F\Value(F\Value::VALID, '50'),
                new F\Value(F\Value::INVALID, 'aa')
            ),
            new F\Test(
                new F\Stack(['fields', 'isCancelled']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, 'ABCDE')
            ),
            new F\Test(
                new F\Stack(['fields', 'cancelledReason']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'isCancelled']), 'N')),
                new F\Value(F\Value::VALID, 'abcdefgh', new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')),
                new F\Value(F\Value::INVALID, '', new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')),
                new F\Value(
                    F\Value::INVALID,
                    str_pad('', 1001, '+'),
                    new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'cancelledDate']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'isCancelled']), 'N')),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'26', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'31', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'26', 'month'=>'13', 'year'=>'2013'],
                    new F\Context(new F\Stack(['fields', 'isCancelled']), 'Y')
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'isAdjourned']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, 'ABCDE')
            ),
            new F\Test(
                new F\Stack(['fields', 'adjournedReason']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'isAdjourned']), 'N')),
                new F\Value(F\Value::VALID, 'abcdefgh', new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')),
                new F\Value(F\Value::INVALID, '', new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')),
                new F\Value(
                    F\Value::INVALID,
                    str_pad('', 1001, '+'),
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'adjournedDate']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'isAdjourned']), 'N')),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'26', 'month'=>'10', 'year'=>'2016', 'hour'=>'08', 'minute'=>'45'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                ),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'26', 'month'=>'10', 'year'=>'2016', 'hour'=>'22', 'minute'=>'30'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'02', 'year'=>'2014', 'hour'=>'22', 'minute'=>'61'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'02', 'year'=>'2014', 'hour'=>'25', 'minute'=>'41'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'30', 'month'=>'02', 'year'=>'2014', 'hour'=>'22', 'minute'=>'41'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'28', 'month'=>'13', 'year'=>'2014', 'hour'=>'22', 'minute'=>'41'],
                    new F\Context(new F\Stack(['fields', 'isAdjourned']), 'Y')
                )
            ),
            //currently no way to create an invalid test here, as this field is only used to populate other fields
            new F\Test(
                new F\Stack(['fields', 'definition']),
                new F\Value(F\Value::VALID, '')
            ),
            new F\Test(
                new F\Stack(['fields', 'details']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            ),
        ];
    }
}
