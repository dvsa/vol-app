<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusServiceNumberAndTypeTest
 *
 * @group FormTests
 */
class BusServiceNumberAndTypeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusServiceNumberAndType::class;

    public function testStartPoint()
    {
        $element = ['fields', 'startPoint'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1, 100);
    }

    public function testFinishPoint()
    {
        $element = ['fields', 'finishPoint'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1, 100);
    }

    public function testVia()
    {
        $element = ['fields', 'via'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1, 255);
    }

    public function testBusServiceTypes()
    {
        $element = ['fields', 'busServiceTypes'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testServiceNo()
    {
        $element = ['fields', 'serviceNo'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1, 70);
    }

    public function testOtherDetails()
    {
        $element = ['fields', 'otherDetails'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 800);
    }

    public function testReceivedDate()
    {
        $element = ['fields', 'receivedDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testEffectiveDate()
    {
        $element = ['fields', 'effectiveDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testEndDate()
    {
        $element = ['fields', 'endDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testBusNoticePeriod()
    {
        $element = ['fields', 'busNoticePeriod'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testIsTxcApp()
    {
        $element = ['fields', 'isTxcApp'];
        $this->assertFormElementHidden($element);
    }

    public function testIsLatestVariation()
    {
        $element = ['fields', 'isLatestVariation'];
        $this->assertFormElementHidden($element);
    }

    public function testId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testStatus()
    {
        $element = ['fields', 'status'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
