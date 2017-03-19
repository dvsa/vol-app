<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Validator\InArray;
use Zend\Form\Element\Button;

/**
 * Class DiscPrintingTest
 *
 * @group FormTests
 */
class DiscPrintingTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\DiscPrinting::class;

    public function testOperatorLocationNiRadio()
    {
        $element = ['operator-location', 'niFlag'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testOperatorType()
    {
        $element = ['operator-type', 'goodsOrPsv'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'lcat_gv');
        $this->assertFormElementValid($element, 'lcat_psv');
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testOperatorLicenceType()
    {
        $element = ['licence-type', 'licenceType'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'ltyp_r');
        $this->assertFormElementValid($element, 'ltyp_sn');
        $this->assertFormElementValid($element, 'ltyp_si');
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testDiscSequence()
    {
        $element = ['prefix', 'discSequence'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testDiscNumberingStart()
    {
        $element = ['discs-numbering', 'startNumber'];
        $this->assertFormElementText($element);
    }

    public function testDiscNumberingEnd()
    {
        $element = ['discs-numbering', 'endNumber'];
        $this->assertFormElementText($element);
    }

    public function testDiscNumberingTotalPages()
    {
        $element = ['discs-numbering', 'totalPages'];
        $this->assertFormElementText($element);
    }

    public function testDiscNumberingOriginalEndNumber()
    {
        $element = ['discs-numbering', 'originalEndNumber'];
        $this->assertFormElementHidden($element);
    }

    public function testDiscNumberingEndNumberIncreased()
    {
        $element = ['discs-numbering', 'endNumberIncreased'];
        $this->assertFormElementHidden($element);
    }

    public function testQueueId()
    {
        $element = ['queueId'];
        $this->assertFormElementHidden($element);
    }

    public function testPrintButton()
    {
        $element = ['form-actions', 'print'];
        $this->assertFormElementActionButton($element);
    }
}
