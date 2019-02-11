<?php

namespace OlcsTest\Form\Model\Form\Licence\Surrender;

use Common\Form\Elements\InputFilters\ActionButton;
use Olcs\Form\Model\Form\Licence\Surrender\Surrender;
use Zend\Form\Element\Checkbox;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

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
    }

    public function testBusRegistrations()
    {
        $element = ['checks', 'busRegistrations'];
        $this->assertFormElementType($element, Checkbox::class);
    }

    public function testSignatureCheck()
    {
        $element = ['checks', 'digitalSignature'];
        $this->assertFormElementType($element, Checkbox::class);
    }

    public function testEcmsCheck()
    {
        $element = ['checks', 'ecms'];
        $this->assertFormElementType($element, Checkbox::class);
    }

    public function testSurrenderAction()
    {
        $element = ['actions', 'surrender'];
        $this->assertFormElementType($element, ActionButton::class);
    }

    public function testWithdrawAction()
    {
        $element = ['actions', 'withdraw'];
        $this->assertFormElementType($element, ActionButton::class);
    }
}
