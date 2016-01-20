<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class PublicationTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PublicationTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\Publication';

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'text1']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 4000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'text2']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 4000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'text3']),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, 'abcdefgh'),
                new F\Value(F\Value::VALID, str_pad('', 4000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            )
        ];
    }
}
