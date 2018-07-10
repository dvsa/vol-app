<?php

namespace PermitsTest\Form\Model\Form;

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
    protected $formName = \Permits\Form\Model\Form\LicenceSelectionForm::class;

    public function testLicence()
    {
        $element = ['Fields', 'Licence'];

        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, "Zend\Form\Element\Radio");
    }

    public function testGuidance()
    {
        $element = ['Fields', 'Guidance'];

        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, "\Common\Form\Elements\Types\GuidanceTranslated");
        $this->assertAttributeEquals("guidance", "data-container-class", $element);
    }


    public function testSubmit()
    {
        $element = ['Submit', 'SubmitButton'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, "Zend\Form\Element\Submit");
    }

}
