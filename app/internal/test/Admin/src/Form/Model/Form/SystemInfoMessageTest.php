<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Validator\InArray;

/**
 * Class SystemInfoMessageTest
 *
 * @group FormTests
 */
class SystemInfoMessageTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\SystemInfoMessage::class;

    public function testDetailsId()
    {
        $element = ['details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testIsInternal()
    {
        $element = ['details', 'isInternal'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid($element, 'C', [InArray::NOT_IN_ARRAY]);
    }

    public function testDescription()
    {
        $element = ['details', 'description'];
        $this->assertFormElementRequired($element, true);
    }

    // @note see assertFormElementDateTime in Abstract class.
    public function testStartDate()
    {
        $element = [ 'details', 'startDate' ];
        $this->assertFormElementDateTime($element);
    }

    // @note see assertFormElementDateTime in Abstract class.
    public function testEndDate()
    {
        $element = [ 'details', 'endDate' ];
        $this->assertFormElementDateTime($element);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
