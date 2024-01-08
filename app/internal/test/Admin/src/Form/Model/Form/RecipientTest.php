<?php

namespace AdminTest\Form\Model\Form;

use OlcsTest\Form\Model\Form\AbstractFormTest;
use Dvsa\OlcsTest\FormTester\Data\Object as F;

/**
 * Class RecipientTest
 * @package AdminTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class RecipientTest extends AbstractFormTest
{
    protected $formName = '\Admin\Form\Model\Form\Recipient';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'trafficAreas'],
                ['A' => 'TA 1', 'B' => 'TA 2']
            ],
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'isObjector']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'contactName']),
                new F\Value(F\Value::VALID, 'aa. abcdefgh abcdefgh abcd-efgh'),
                new F\Value(F\Value::VALID, str_pad('', 100, '+')),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, str_pad('', 101, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'emailAddress']),
                new F\Value(F\Value::VALID, 'test@foobar.com'),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'test'),
                new F\Value(F\Value::INVALID, 'foobar.com')
            ),
            new F\Test(
                new F\Stack(['fields', 'sendAppDecision']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N', new F\Context(new F\Stack(['fields', 'sendNoticesProcs']), 'Y')),
                new F\Value(F\Value::INVALID, 'N', new F\Context(new F\Stack(['fields', 'sendNoticesProcs']), 'N')),
                new F\Value(F\Value::INVALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, 'ABCDE')
            ),
            new F\Test(
                new F\Stack(['fields', 'sendNoticesProcs']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, 'ABCDE')
            ),
            new F\Test(
                new F\Stack(['fields', 'trafficAreas']),
                new F\Value(F\Value::VALID, ['A']),
                new F\Value(F\Value::VALID, ['A', 'B']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, '')
            ),
            new F\Test(
                new F\Stack(['fields', 'isPolice']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, 'ABCDE')
            ),
        ];
    }
}
