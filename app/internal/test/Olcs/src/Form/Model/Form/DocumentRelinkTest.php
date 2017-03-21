<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Select;
use Zend\Validator\InArray;

/**
 * Class DocumentRelinkTest
 *
 * @group FormTests
 */
class DocumentRelinkTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\DocumentRelink::class;

    public function testIds()
    {
        $element = ['document-relink-details', 'ids'];
        $this->assertFormElementHidden($element);
    }

    public function testRelinkTo()
    {
        $element = ['document-relink-details', 'relinkTo'];
        $this->assertFormElementHtml($element);
    }

    public function testType()
    {
        $element = ['document-relink-details', 'type'];
        $this->assertFormElementType($element, Select::class);

        $this->assertFormElementNotValid(
            $element,
            'XXX',
            [InArray::NOT_IN_ARRAY]
        );

        $this->assertFormElementValid($element, 'application');
        $this->assertFormElementValid($element, 'busReg');
        $this->assertFormElementValid($element, 'case');
        $this->assertFormElementValid($element, 'licence');
        $this->assertFormElementValid($element, 'irfoOrganisation');
        $this->assertFormElementValid($element, 'transportManager');
    }

    public function testTargetId()
    {
        $element = ['document-relink-details', 'targetId'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 255);
    }

    public function testCopyButton()
    {
        $element = ['form-actions', 'copy'];
        $this->assertFormElementActionButton($element);
    }

    public function testMoveButton()
    {
        $element = ['form-actions', 'move'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
