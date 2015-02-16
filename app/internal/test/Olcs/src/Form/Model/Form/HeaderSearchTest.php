<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class PublicInquiryAgreedAndLegislationTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class HeaderSearchTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\HeaderSearch';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['index'],
                ['app' => 'Application', 'case' => 'Case']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['search']),
                new F\Value(F\Value::VALID, 'string'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['index']),
                new F\Value(F\Value::VALID, 'case'),
                new F\Value(F\Value::INVALID, null)
            )
        ];
    }
}
