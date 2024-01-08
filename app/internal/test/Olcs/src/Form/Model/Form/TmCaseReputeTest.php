<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\Data\Object as F;

/**
 * Class TmCaseReputeTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class TmCaseReputeTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\TmCaseRepute';

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'isMsi']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'decisionDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'13', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'aaa']),
                new F\Value(F\Value::INVALID, ['day'=>'32', 'month'=>'09', 'year'=>'2013'])
            ),
            new F\Test(
                new F\Stack(['fields', 'notifiedDate']),
                new F\Value(
                    F\Value::VALID,
                    '',
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'26', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'26', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'26', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'27', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'26', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'25', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'26', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'26', 'month'=>'02', 'year'=>'aaa'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'26', 'month'=>'13', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'32', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'decisionDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'reputeNotLostReason']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 500, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 501, '+'))
            )
        ];
    }
}
