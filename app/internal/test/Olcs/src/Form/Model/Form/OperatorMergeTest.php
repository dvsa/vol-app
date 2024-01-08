<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Form\Element\Select;

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

    public function testLicenceIds()
    {
        $this->assertFormElementDynamicSelect(['licenceIds'], false);
    }

    public function testOperatorName()
    {
        $this->assertFormElementIsRequired(['fromOperatorName'], false);
    }

    public function testToOperatorId()
    {
        $this->assertFormElementIsRequired(['toOperatorId'], true);
    }

    public function testConfirm()
    {
        $this->assertFormElementIsRequired(['confirm'], true);
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
}
