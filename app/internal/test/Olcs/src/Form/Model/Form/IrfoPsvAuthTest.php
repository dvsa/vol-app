<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Form\Element\Select;

/**
 * Class IrfoPsvAuthTest
 *
 * @group FormTests
 */
class IrfoPsvAuthTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\IrfoPsvAuth::class;

    public function testIrfoPsvAuthType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'irfoPsvAuthType'],
            true
        );
    }

    public function testValidityType()
    {
        $element = ['fields', 'validityPeriod'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testStatusHtml()
    {
        $this->assertFormElementHtml(['fields', 'statusHtml']);
    }

    public function testStatus()
    {
        $this->assertFormElementHidden(['fields', 'status']);
    }

    public function testStatusDescription()
    {
        $this->assertFormElementHidden(['fields', 'statusDescription']);
    }

    public function testIrfoFileNo()
    {
        $this->assertFormElementHtml(['fields', 'irfoFileNo']);
    }

    public function testCreatedOnHtml()
    {
        $this->assertFormElementHtml(['fields', 'createdOnHtml']);
    }

    public function testInForceDate()
    {
        $this->assertFormElementDate(['fields', 'inForceDate']);
    }

    public function testExpiryDate()
    {
        $element = ['fields', 'expiryDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid(
            $element,
            ['day' => 2, 'month' => '2', 'year' => 2010],
            [
                'fields' => [
                    'inForceDate' => [
                        'day'   => 1,
                        'month' => '2',
                        'year'  => 2010,
                    ],
                ],
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => 1, 'month' => '2', 'year' => 2010],
            \Common\Validator\AbstractCompare::NOT_GTE,
            [
                'fields' => [
                    'inForceDate' => [
                        'day'   => 2,
                        'month' => '2',
                        'year'  => 2010,
                    ],
                ],
            ]
        );
    }

    public function testApplicationSentDate()
    {
        $this->assertFormElementDate(['fields', 'applicationSentDate']);
    }

    public function testServiceRouteFrom()
    {
        $element = ['fields', 'serviceRouteFrom'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 30);
    }

    public function testServiceRouteTo()
    {
        $element = ['fields', 'serviceRouteTo'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 1, 30);
    }

    public function testJourneyFrequency()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'journeyFrequency'],
            true
        );
    }

    public function testCountries()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'countrys'],
            true
        );
    }

    public function testIsFeeExemptApplication()
    {
        $element = ['fields', 'isFeeExemptApplication'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testIsFeeExemptAnnual()
    {
        $element = ['fields', 'isFeeExemptAnnual'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testExemptionDetails()
    {
        $element = ['fields', 'exemptionDetails'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testCopiesIssuedHtml()
    {
        $this->assertFormElementHtml(['fields', 'copiesIssuedHtml']);
    }

    public function testCopiesIssuedTotalHtml()
    {
        $this->assertFormElementHtml(['fields', 'copiesIssuedTotalHtml']);
    }

    public function testCopiesRequired()
    {
        $this->assertFormElementRequired(
            ['fields', 'copiesRequired'],
            true
        );

        $this->assertFormElementNumber(['fields', 'copiesRequired'], 0, 500);
    }

    public function testCopiesRequiredNonChargeable()
    {
        $this->assertFormElementRequired(
            ['fields', 'copiesRequiredNonChargeable'],
            true
        );

        $this->assertFormElementNumber(['fields', 'copiesRequiredNonChargeable'], 0, 500);
    }

    public function testCopiesRequiredTotal()
    {
        $this->assertFormElementRequired(
            ['fields', 'copiesRequiredTotal'],
            true
        );

        $this->assertFormElementNumber(['fields', 'copiesRequiredTotal'], 0, 1000);
    }

    public function testIsGrantable()
    {
        $this->assertFormElementHidden(['fields', 'isGrantable']);
    }

    public function testIsApprovable()
    {
        $this->assertFormElementHidden(['fields', 'isApprovable']);
    }

    public function testIsDocumentable()
    {
        $this->assertFormElementHidden(['fields', 'isDocumentable']);
    }

    public function testIsCnsable()
    {
        $this->assertFormElementHidden(['fields', 'isCnsable']);
    }

    public function testIsWithdrawable()
    {
        $this->assertFormElementHidden(['fields', 'isWithdrawable']);
    }

    public function testIsRefusable()
    {
        $this->assertFormElementHidden(['fields', 'isRefusable']);
    }

    public function testIsResetable()
    {
        $this->assertFormElementHidden(['fields', 'isResetable']);
    }

    public function testOrganisation()
    {
        $this->assertFormElementHidden(['fields', 'organisation']);
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
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testGrant()
    {
        $element = ['form-actions', 'grant'];
        $this->assertFormElementActionButton($element);
    }

    public function testApprove()
    {
        $element = ['form-actions', 'approve'];
        $this->assertFormElementActionButton($element);
    }

    public function testGenerate()
    {
        $element = ['form-actions', 'generate'];
        $this->assertFormElementActionButton($element);
    }

    public function testCns()
    {
        $element = ['form-actions', 'cns'];
        $this->assertFormElementActionButton($element);
    }

    public function testWithdraw()
    {
        $element = ['form-actions', 'withdraw'];
        $this->assertFormElementActionButton($element);
    }

    public function testRefuse()
    {
        $element = ['form-actions', 'refuse'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
