<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;
use Laminas\Validator\InArray;

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
        $this->assertFormElementHidden(['document-relink-details', 'ids']);
    }

    public function testRelinkTo()
    {
        $this->assertFormElementHtml(['document-relink-details', 'relinkTo']);
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
        $this->assertFormElementActionButton(['form-actions', 'copy']);
    }

    public function testMoveButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'move']);
    }

    public function testCancelButton()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
