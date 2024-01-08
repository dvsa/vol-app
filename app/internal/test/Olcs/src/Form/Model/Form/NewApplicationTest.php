<?php

namespace OlcsTest\Form\Model\Form;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class NewApplicationTest
 *
 * @group FormTests
 */
class NewApplicationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\NewApplication::class;

    public function testReceivedDate()
    {
        $this->assertFormElementDate(['details', 'receivedDate']);
    }

    public function testTrafficArea()
    {
        $this->assertFormElementRequired(['details', 'trafficArea'], true);
    }

    public function testOperatorLocation()
    {
        $this->assertFormElementRequired(
            ['type-of-licence', 'operator-location'],
            true
        );
    }

    public function testOperatorType()
    {
        $this->assertFormElementRequired(
            ['type-of-licence', 'operator-type'],
            true
        );
    }

    public function testLicenceType()
    {
        $this->assertFormElementRequired(
            ['type-of-licence', 'licence-type', 'licence-type'],
            true
        );
    }

    public function testVehicleType()
    {
        $element = ['type-of-licence', 'licence-type', 'ltyp_siContent', 'vehicle-type'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testLgvDeclarationConfirmation()
    {
        $element = [
            'type-of-licence',
            'licence-type',
            'ltyp_siContent',
            'lgv-declaration',
            'lgv-declaration-confirmation'
        ];

        $this->assertFormElementType($element, SingleCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testLgvDeclarationWarning()
    {
        $element = [
            'type-of-licence',
            'licence-type',
            'ltyp_siContent',
            'lgv-declaration',
            'lgvDeclarationWarning'
        ];

        $this->assertFormElementHtml($element);
    }

    public function testAppliedVia()
    {
        $element = ['appliedVia'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementValid($element, 'applied_via_post');
        $this->assertFormElementValid($element, 'applied_via_phone');
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
