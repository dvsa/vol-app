<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator;

/**
 * Class IrfoGvPermitTest
 *
 * @group FormTests
 */
class IrfoGvPermitTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\IrfoGvPermit::class;

    public function testIrfoGvPermitType()
    {
        $element = ['fields', 'irfoGvPermitType'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testYearRequired()
    {
        $element = ['fields', 'yearRequired'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementNumber($element);
    }

    public function testInForceDate()
    {
        $element = ['fields', 'inForceDate'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDate($element);
    }

    public function testExpiryDate()
    {
        $element = ['fields', 'expiryDate'];
        $this->assertFormElementIsRequired($element, true);
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

    public function testIsFeeExempt()
    {
        $element = ['fields', 'isFeeExempt'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementCheckbox($element);
    }

    public function testExemptionDetails()
    {
        $element = ['fields', 'exemptionDetails'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);

        $this->assertFormElementAllowEmpty(
            $element,
            false,
            ['fields' => ['isFeeExempt' => 'Y']]
        );

        $this->assertFormElementText(
            $element,
            0,
            255,
            ['fields' =>
                 ['isFeeExempt' => 'Y']
            ]
        );
    }

    public function testNoOfCopies()
    {
        $element = ['fields', 'noOfCopies'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementNumber($element);
    }

    public function testOrganisation()
    {
        $element = ['fields', 'organisation'];
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
