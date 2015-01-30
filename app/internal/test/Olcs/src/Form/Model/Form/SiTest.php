<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class SiTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\Si';

    protected function getDynamicSelectData()
    {
        /*return [
            [
                ['fields', 'hearingType'],
                ['1' => 'HT 1', '2' => 'HT 2']
            ],
            [
                ['fields', 'venue'],
                ['23' => 'VENUE 1', '24' => 'VENUE 2']
            ],
            [
                ['fields', 'presidingTc'],
                ['1' => 'TC 1', '2' => 'TC 2']
            ]
        ];*/

        return [];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'notificationNumber']),
                new F\Value(F\Value::VALID, 'ABC123'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::INVALID, 'A')
            ),
        ];
    }
}
