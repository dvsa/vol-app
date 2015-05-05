<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;
use Admin\Form\Model\Form\Partner as PartnerForm;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PartnerTest extends AbstractFormTest
{
    protected $formName = PartnerForm::class;

    protected function getDynamicSelectData()
    {
        return [
            [
                ['address', 'countryCode'],
                ['GB' => 'United Kingdom', 'FR' => 'France']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'description']),
                new F\Value(F\Value::VALID, 'Some Partner Name'),
                new F\Value(F\Value::INVALID, '11'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'contactType']),
                new F\Value(F\Value::VALID, 'ct_partner')
            )
        ];
    }
}
