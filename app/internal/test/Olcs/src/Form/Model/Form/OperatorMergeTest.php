<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Zend\Form\Element\Select;

/**
 * Class OperatorMergeTest
 *
 * @group FormTests
 */
class OperatorMergeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\OperatorMerge::class;

    public function testOperatorName()
    {
        $this->assertFormElementRequired(['fromOperatorName'], false);
    }

    public function testToOperatorId()
    {
        $this->assertFormElementRequired(['toOperatorId'], true);
    }

    public function testConfirm()
    {
        $this->assertFormElementRequired(['confirm'], true);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testLicenceIds()
    {
        $element = ['licenceIds'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementIsRequired($element, false);
    }
}
