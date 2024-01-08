<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

/**
 * Class FeeTest
 *
 * @group FormTests
 */
class FeeTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Fee::class;

    public function testId()
    {
        $this->assertFormElementHidden(['fee-details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fee-details', 'version']);
    }

    public function testWaiveRemainder()
    {
        $element = ['fee-details', 'waiveRemainder'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testWaiveReason()
    {
        $element = ['fee-details', 'waiveReason'];

        $this->assertFormElementRequired(
            $element,
            true,
            [StringLength::INVALID, NotEmpty::IS_EMPTY]
        );

        $this->assertFormElementAllowEmpty(
            $element,
            false,
            [],
            [StringLength::TOO_SHORT]
        );

        $this->assertFormElementText($element, 5, 255);
    }

    public function testPay()
    {
        $this->assertFormElementActionButton(['form-actions', 'pay']);
    }

    public function testRecommend()
    {
        $this->assertFormElementActionButton(['form-actions', 'recommend']);
    }

    public function testApprove()
    {
        $this->assertFormElementActionButton(['form-actions', 'approve']);
    }

    public function testReject()
    {
        $this->assertFormElementActionButton(['form-actions', 'reject']);
    }

    public function testRefund()
    {
        $this->assertFormElementActionButton(['form-actions', 'refund']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
