<?php

namespace OlcsTest\FormTest;

use Common\FormTester\Data\Object as F;

/**
 * Class PublicInquiryAgreedAndLegislationTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PublicInquiryRegisterDecisionTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\PublicInquiryRegisterDecision';

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'decisions']),
                new F\Value(F\Value::VALID, '', new F\Context(new F\Stack(['fields', 'decisionDate']), null))
            )
        ];
    }
}
 