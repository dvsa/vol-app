<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class OperatorTest
 *
 * @group FormTests
 */
class OperatorTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Operator::class;

    public function testOperatorId()
    {
        $this->assertFormElementRequired(['operator-id', 'operator-id'], false);
    }

    public function testOperatorBusinessType()
    {
        $this->assertFormElementDynamicSelect(
            ['operator-business-type', 'type'], false
        );
    }

    public function testOperatorBusinessTypeRefreshButton()
    {
        $this->assertFormElementActionButton(
            ['operator-business-type', 'refresh']
        );
    }

    public function testOperatorDetailsId()
    {
        $this->assertFormElementHidden(['operator-details', 'id']);
    }

    public function testOperatorDetailsVersion()
    {
        $this->assertFormElementHidden(['operator-details', 'version']);
    }

    public function testOperatorDetailsName()
    {
        $this->assertFormElementRequired(['operator-details', 'name'], true);
    }

    public function testOperatorDetailsCompanyNumber()
    {
        $this->assertFormElementCompanyNumberType(
            ['operator-details', 'companyNumber']
        );
    }

    public function testNatureOfBusiness()
    {
        $this->assertFormElementRequired(
            ['operator-details', 'natureOfBusiness'],
            true
        );
    }

    public function testOperatorDetailsInformation()
    {
        $this->assertFormElementHtml(
            ['operator-details', 'information']
        );
    }

    public function testFirstname()
    {
        $this->assertFormElementRequired(
            ['operator-details', 'firstName'],
            false
        );
    }

    public function testLastname()
    {
        $this->assertFormElementRequired(
            ['operator-details', 'lastName'],
            true
        );
    }

    public function testIsIrfo()
    {
        $element = ['operator-details', 'isIrfo'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testAllowEmail()
    {
        $this->assertFormElementRequired(
            ['operator-details', 'allowEmail'],
            true
        );
    }

    public function testOperatorDetailsPersonId()
    {
        $this->assertFormElementHidden(['operator-details', 'personId']);
    }

    public function testOperatorDetailsPersonVersion()
    {
        $this->assertFormElementHidden(['operator-details', 'personVersion']);
    }

    public function testRegisteredAddressId()
    {
        $this->assertFormElementHidden(['registeredAddress', 'id']);
    }

    public function testRegisteredAddressVersion()
    {
        $this->assertFormElementHidden(['registeredAddress', 'version']);
    }

    public function testRegisteredAddressAddressLine1()
    {
        $element = ['registeredAddress', 'addressLine1'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
    }

    public function testRegisteredAddressAddressLine2()
    {
        $element = ['registeredAddress', 'addressLine2'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testRegisteredAddressAddressLine3()
    {
        $element = ['registeredAddress', 'addressLine3'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testRegisteredAddressAddressLine4()
    {
        $element = ['registeredAddress', 'addressLine4'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testRegisteredAddressTown()
    {
        $element = ['registeredAddress', 'town'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
    }

    public function testRegisteredAddressPostcode()
    {
        $element = ['registeredAddress', 'postcode'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementPostcode($element);
    }

    public function testCpidType()
    {
        $this->assertFormElementDynamicSelect(['operator-cpid', 'type'], false);
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'save']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
