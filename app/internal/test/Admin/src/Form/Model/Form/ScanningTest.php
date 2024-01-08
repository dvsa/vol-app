<?php

namespace AdminTest\Form\Model\Form;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class ScanningTest
 *
 * @group FormTests
 */
class ScanningTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\Scanning::class;

    public function testScanningDetailsCategory()
    {
        $element = ['details', 'category'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testScanningDetailsSubCategory()
    {
        $element = ['details', 'subCategory'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testScanningDetailsDescription()
    {
        $element = ['details', 'description'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testScanningDetailsOtherDescription()
    {
        $element = ['details', 'otherDescription'];
        $this->assertFormElementRequired($element, true);
    }

    public function testScanningDetailsEntityIdentifier()
    {
        $element = ['details', 'entityIdentifier'];
        $this->assertFormElementRequired($element, true);
    }

    public function testScanningDetailsBackScan()
    {
        $element = ['details', 'backScan'];
        $this->assertFormElementType($element, SingleCheckbox::class);
        $this->assertFormElementRequired($element, false);
    }

    public function testScanningDetailsDateReceived()
    {
        $element = ['details', 'dateReceived'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, true);
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
