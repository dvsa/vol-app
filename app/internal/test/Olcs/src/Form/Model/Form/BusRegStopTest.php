<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class BusRegStopTest
 *
 * @group FormTests
 */
class BusRegStopTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegStop::class;

    public function testUseAllStops()
    {
        $element = ['fields', 'useAllStops'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testHasManoeuvre()
    {
        $element = ['fields', 'hasManoeuvre'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testManoeuvreDetail()
    {
        $element = ['fields', 'manoeuvreDetail'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testNeedNewStop()
    {
        $element = ['fields', 'needNewStop'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testNewStopDetail()
    {
        $element = ['fields', 'newStopDetail'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testHasNotFixedStop()
    {
        $element = ['fields', 'hasNotFixedStop'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testNotFixedStopDetail()
    {
        $element = ['fields', 'notFixedStopDetail'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testSubsidised()
    {
        $element = ['fields', 'subsidised'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testSubsidyDetail()
    {
        $element = ['fields', 'subsidyDetail'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
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
