<?php

namespace PermitsTest\Form\Model\Form;

use Common\Form\Elements\Types\Radio;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class LicenceSelectionFormTest
 *
 * @group FormTests
 */
class LicenceSelectionFormTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Permits\Form\Model\Form\LicenceForm::class;

    public function testLicence()
    {
        $element = ['fields', 'licence'];
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }
}
