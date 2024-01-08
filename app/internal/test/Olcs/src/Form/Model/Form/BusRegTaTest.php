<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class BusRegTaTest
 *
 * @group FormTests
 */
class BusRegTaTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegTa::class;

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

    public function testTrafficAreas()
    {
        $element = ['fields', 'trafficAreas'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testLocalAuthoritys()
    {
        $element = ['fields', 'localAuthoritys'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testStoppingArrangements()
    {
        $element = ['fields', 'stoppingArrangements'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 800);
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
