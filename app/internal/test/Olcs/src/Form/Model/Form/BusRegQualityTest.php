<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class BusRegQualityTest
 *
 * @group FormTests
 */
class BusRegQualityTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\BusRegQuality::class;

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

    public function testIsLatestVariation()
    {
        $element = ['fields', 'isLatestVariation'];
        $this->assertFormElementHidden($element);
    }

    public function testIsTxcApp()
    {
        $element = ['fields', 'isTxcApp'];
        $this->assertFormElementHidden($element);
    }

    public function testIsQualityPartnership()
    {
        $element = ['fields', 'isQualityPartnership'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testQualityPartnershipDetails()
    {
        $element = ['fields', 'qualityPartnershipDetails'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 4000);
    }

    public function testIsQualityContract()
    {
        $element = ['fields', 'isQualityContract'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testQualityPartnershipFacilitiesUsed()
    {
        $element = ['fields', 'qualityPartnershipFacilitiesUsed'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, Radio::class);
    }

    public function testQualityContractDetails()
    {
        $element = ['fields', 'qualityContractDetails'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 4000);
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
