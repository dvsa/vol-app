<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TmCaseUnfitTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class TmCaseUnfitTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\TmCaseUnfit';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'unfitnessReasons'],
                ['tm_unfit_inc' => 'Reason 1']
            ],
            [
                ['fields', 'rehabMeasures'],
                ['tm_rehab_adc' => 'Measure 1']
            ],
        ];
    }

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
                new F\Stack(['fields', 'unfitnessStartDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'13', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'aaa']),
                new F\Value(F\Value::INVALID, ['day'=>'32', 'month'=>'09', 'year'=>'2013'])
            ),
            new F\Test(
                new F\Stack(['fields', 'unfitnessEndDate']),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'26', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'unfitnessStartDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::VALID,
                    ['day'=>'25', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'unfitnessStartDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'24', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'unfitnessStartDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                ),
                new F\Value(
                    F\Value::INVALID,
                    ['day'=>'32', 'month'=>'02', 'year'=>'2013'],
                    new F\Context(
                        new F\Stack(['fields', 'unfitnessStartDate']),
                        ['day'=>'25', 'month'=>'02', 'year'=>'2013']
                    )
                )
            ),
            new F\Test(
                new F\Stack(['fields', 'unfitnessReasons']),
                new F\Value(F\Value::VALID, 'tm_unfit_inc'),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'aaa')
            ),
            new F\Test(
                //unable to generate invalid test currently
                new F\Stack(['fields', 'rehabMeasures']),
                new F\Value(F\Value::VALID, 'tm_rehab_adc'),
                new F\Value(F\Value::VALID, '')
            )
        ];
    }
}
