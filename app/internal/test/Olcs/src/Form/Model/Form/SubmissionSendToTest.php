<?php

namespace OlcsTest\Form\Model\Form;

use Common\Validator\Date as CommonDateValidator;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Date as LaminasDateValidator;

/**
 * Class SubmissionSendToTest
 *
 * @group FormTests
 */
class SubmissionSendToTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SubmissionSendTo::class;

    public function testRecipientUser()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'recipientUser'],
            true
        );
    }

    public function testSenderUser()
    {
        $this->assertFormElementHidden(['fields', 'senderUser']);
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFieldsInformationCompleteDate()
    {
        $this->markTestSkipped();
        $element = ['fields', 'informationCompleteDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        $this->assertFormElementValid($element, ['day' => 1, 'month' => '2', 'year' => 1999]);
        $this->assertFormElementNotValid(
            $element,
            ['day' => 'X', 'month' => '2', 'year' => 1999],
            [
                LaminasDateValidator::INVALID_DATE,
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => 'X', 'year' => 1999],
            [
                LaminasDateValidator::INVALID_DATE,
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => 1, 'month' => 3, 'year' => 'XXXX'],
            [
                LaminasDateValidator::INVALID_DATE,
            ]
        );
    }

    /**
     * @todo unskip https://jira.dvsacloud.uk/browse/VOL-2309
     */
    public function testFieldsAssignedDate()
    {
        $this->markTestSkipped();
        $element = ['fields', 'assignedDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => '2', 'year' => '1999'],
            [
                'invalidField',
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => 'X', 'month' => '2', 'year' => '1999'],
            [
                CommonDateValidator::DATE_ERR_CONTAINS_STRING,
                LaminasDateValidator::INVALID_DATE,
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => 'X', 'year' => '1999'],
            [
                CommonDateValidator::DATE_ERR_CONTAINS_STRING,
                LaminasDateValidator::INVALID_DATE,
            ]
        );
        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => '3', 'year' => 'XXXX'],
            [
                CommonDateValidator::DATE_ERR_CONTAINS_STRING,
                CommonDateValidator::DATE_ERR_YEAR_LENGTH,
                LaminasDateValidator::INVALID_DATE,
            ]
        );
        $this->assertFormElementValid(
            ['fields'],
            [
                'recipientUser' => 'user',
                'urgent' => 'N',
                'informationCompleteDate' => ['day' => '1', 'month' => '2', 'year' => '1999'],
                'assignedDate' => ['day' => '1', 'month' => '2', 'year' => '1999'],
            ]
        );
        $this->assertFormElementValid(
            ['fields'],
            [
                'recipientUser' => 'user',
                'urgent' => 'N',
                'informationCompleteDate' => ['day' => '1', 'month' => '2', 'year' => '1999'],
                'assignedDate' => ['day' => '1', 'month' => '3', 'year' => '1999'],
            ]
        );
        $this->assertFormElementNotValid(
            ['fields'],
            [
                'recipientUser' => 'user',
                'urgent' => 'N',
                'informationCompleteDate' => ['day' => '1', 'month' => '2', 'year' => '1999'],
                'assignedDate' => ['day' => '1', 'month' => '1', 'year' => '1999'],
            ],
            [
                'informationCompleteDate',
                'assignedDate',
            ]
        );
    }

    public function testUrgent()
    {
        $this->assertFormElementRequired(['fields', 'urgent'], true);
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
