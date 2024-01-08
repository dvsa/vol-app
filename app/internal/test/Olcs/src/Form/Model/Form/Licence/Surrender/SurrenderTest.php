<?php

namespace OlcsTest\Form\Model\Form\Licence\Surrender;

use Olcs\Form\Model\Form\Licence\Surrender\Surrender;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Checkbox;
use Laminas\Validator;

class SurrenderTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = Surrender::class;

    public function testOpenCases()
    {
        $element = ['checks', 'openCases'];
        $this->assertFormElementType($element, Checkbox::class);
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementCheckbox($element, '0', '1');
    }

    public function testBusRegistrations()
    {
        $element = ['checks', 'busRegistrations'];
        $this->assertFormElementType($element, Checkbox::class);
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementCheckbox($element, '0', '1');
    }

    public function testSignatureCheck()
    {
        $element = ['checks', 'digitalSignature'];
        $this->assertFormElementType($element, Checkbox::class);
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, '1');
        $this->assertFormElementNotValid($element, '0', [Validator\GreaterThan::NOT_GREATER]);
        $this->assertFormElementNotValid(
            $element,
            'X',
            [
                Validator\InArray::NOT_IN_ARRAY,
                Validator\GreaterThan::NOT_GREATER,
            ]
        );
    }

    public function testEcmsCheck()
    {
        $element = ['checks', 'ecms'];
        $this->assertFormElementType($element, Checkbox::class);
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, '1');
        $this->assertFormElementNotValid($element, '0', [Validator\GreaterThan::NOT_GREATER]);
        $this->assertFormElementNotValid(
            $element,
            'X',
            [
                Validator\InArray::NOT_IN_ARRAY,
                Validator\GreaterThan::NOT_GREATER,
            ]
        );
    }

    public function testSurrenderAction()
    {
        $element = ['actions', 'surrender'];
        $this->assertFormElementActionButton($element);
    }

    public function testWithdrawAction()
    {
        $element = ['actions', 'withdraw'];
        $this->assertFormElementActionLink($element);
    }
}
