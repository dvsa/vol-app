<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\Olcs\Transfer\Validators\Money;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\InArray;
use Laminas\Form\Element\Radio;

/**
 * Class FinancialStandingRateTest
 *
 * @group FormTests
 */
class FinancialStandingRateTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\FinancialStandingRate::class;

    public function testSubmitAndCancelButton()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );

        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }

    public function testFinancialStandingRateType()
    {
        $element = ['details', 'goodsOrPsv'];

        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'lcat_gv');
        $this->assertFormElementValid($element, 'lcat_psv');

        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testLicenceType()
    {
        $element = ['details', 'licenceType'];

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

    public function testVehicleType()
    {
        $element = ['details', 'vehicleType'];

        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementValid($element, 'fin_sta_veh_typ_na');
        $this->assertFormElementValid($element, 'fin_sta_veh_typ_hgv');
        $this->assertFormElementValid($element, 'fin_sta_veh_typ_lgv');

        $this->assertFormElementNotValid(
            $element,
            'X',
            [InArray::NOT_IN_ARRAY]
        );
    }

    public function testFirstVehicleRate()
    {
        $element = ['details', 'firstVehicleRate'];

        $this->assertFormElementValid($element, 1.10);
        $this->assertFormElementValid($element, 10.30);

        $this->assertFormElementNotValid(
            $element,
            'abc',
            ['invalid']
        );
    }

    public function testAdditionalVehicleRate()
    {
        $element = ['details', 'additionalVehicleRate'];

        $this->assertFormElementValid($element, 1.10);
        $this->assertFormElementValid($element, 10.30);

        $this->assertFormElementNotValid(
            $element,
            'abc',
            ['invalid']
        );
    }

    public function testEffectiveFrom()
    {
        $element = ['details', 'effectiveFrom'];

        $pastYear = date('Y') - 1;

        $errorMessages = [
            'dateInvalidDate',
        ];

        $this->assertFormElementValid(
            $element,
            ['day' => 1, 'month' => '2', 'year' => $pastYear]
        );

        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => '1', 'year' => 'ABC'],
            $errorMessages
        );
    }

    public function testId()
    {
        $this->assertFormElementHidden(['details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['details', 'version']);
    }
}
