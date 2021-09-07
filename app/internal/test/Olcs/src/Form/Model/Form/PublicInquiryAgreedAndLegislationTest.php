<?php

namespace OlcsTest\Form\Model\Form;

use Common\Form\Elements\Validators\DateNotInFuture;
use Olcs\Form\Model\Form\PublicInquiryAgreedAndLegislation;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Olcs\Validator\TypeOfPI;

class PublicInquiryAgreedAndLegislationTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = PublicInquiryAgreedAndLegislation::class;

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFieldsAgreedDate()
    {
        $this->markTestSkipped();
        $element = ['fields', 'agreedDate'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementNotValid(
            $element,
            [
                'day' => '15',
                'month' => '06',
                'year' => '2060'
            ],
            [DateNotInFuture::IN_FUTURE]
        );
        $this->assertFormElementDate($element);
    }

    public function testFieldsAgreedByTc()
    {
        $element = ['fields', 'agreedByTc'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testFieldsAgreedByTcRole()
    {
        $element = ['fields', 'agreedByTcRole'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testFieldsAssignedCaseworker()
    {
        $element = ['fields', 'assignedCaseworker'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testFieldsIsEcmsCase()
    {
        $element = ['fields', 'isEcmsCase'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFieldsEcmsFirstReceivedDate()
    {
        $this->markTestSkipped();
        $element = ['fields', 'ecmsFirstReceivedDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementNotValid(
            $element,
            [
                'day' => '15',
                'month' => '06',
                'year' => '2060'
            ],
            [DateNotInFuture::IN_FUTURE]
        );
        $this->assertFormElementDate($element, false);
    }

    public function testFieldsPiTypes()
    {
        $element = ['fields', 'piTypes'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);

        $this->assertFormElementValid($element, ['pi_t_tm_only']);
        $this->assertFormElementValid($element, ['type2', 'type3']);
        $this->assertFormElementNotValid($element, ['pi_t_tm_only', 'type2'], TypeOfPI::TM_ONLY);
    }

    public function testFieldsReasons()
    {
        $element = ['fields', 'reasons'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementDynamicSelect($element);
    }

    public function testFieldsComment()
    {
        $element = ['fields', 'comment'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testFieldsCase()
    {
        $element = ['fields', 'case'];
        $this->assertFormElementHidden($element);
    }

    public function testFieldsId()
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testFieldsVersion()
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testFormActionsSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testFormActionsCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
