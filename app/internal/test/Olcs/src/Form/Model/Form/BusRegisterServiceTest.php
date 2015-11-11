<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class BusRegisterServiceTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class BusRegisterServiceTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\BusRegisterService';

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['timetable', 'timetableAcceptable']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['timetable', 'mapSupplied']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['timetable', 'routeDescription']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 1000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 1001, '+'))
            ),
            new F\Test(
                new F\Stack(['conditions', 'trcConditionChecked']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['conditions', 'trcNotes']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 255, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'copiedToLaPte']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'laShortNote']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'applicationSigned']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'opNotifiedLaPte']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            )
        ];
    }
}
