<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class SiTest
 *
 * @group FormTests
 */
class SiTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Si::class;

    public function testNotificationNumber()
    {
        $element = ['fields', 'notificationNumber'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 36);
    }

    public function testSiCategoryType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'siCategoryType'],
            true
        );
    }

    public function testInfringementDate()
    {
        $this->assertFormElementDate(['fields', 'infringementDate']);
    }

    public function testCheckDate()
    {
        $this->assertFormElementDate(['fields', 'checkDate']);
    }

    public function testMemberStateCode()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'memberStateCode'],
            true
        );
    }

    public function testReason()
    {
        $element = ['fields', 'reason'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 5000);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
