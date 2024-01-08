<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\Data\Object as F;

/**
 * Class BusShortNoticeTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class BusShortNoticeTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\BusShortNotice';

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'bankHolidayChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'unforseenChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'unforseenDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'timetableChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'timetableDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'replacementChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'replacementDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'notAvailableChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'notAvailableDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'specialOccasionChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'specialOccasionDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'connectionChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'connectionDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'holidayChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'holidayDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'trcChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'trcDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'policeChange']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'A'),
                new F\Value(F\Value::INVALID, ['ABCDE'])
            ),
            new F\Test(
                new F\Stack(['fields', 'policeDetail']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::INVALID, str_pad('', 256, '+'))
            ),
        ];
    }
}
