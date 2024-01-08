<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Validator\Date as CommonDateValidator;
use Laminas\Validator\Date;

/**
 * Class PublicInquirySlaTest
 *
 * @group FormTests
 */
class PublicInquirySlaTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\PublicInquirySla::class;

    public function testCallUpLetterDate()
    {
        $element = ['fields', 'callUpLetterDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testBriefToTcDate()
    {
        $element = ['fields', 'briefToTcDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testWrittenOutcome()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'writtenOutcome'],
            false
        );
    }

    /**
     * @dataProvider dpTestDateFieldsWithComplexValidation
     */
    public function testDateFieldsWithComplexValidation(
        $elementName,
        $writtenOutcomeValue
    ) {
        $element = ['fields', $elementName];

        // No context to validateIf(writtenOutcome=piwo_decision) should pass
        $this->assertFormElementValid(
            $element,
            [
                'year'  => 'XXX',
                'month' => date('m'),
                'day'   => date('j'),
            ]
        );

        // Context with validateIf(writtenOutcome=piwo_decision) should fail
        // With invalid date
        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => 'XXX',
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                CommonDateValidator::DATE_ERR_CONTAINS_STRING,
                CommonDateValidator::DATE_ERR_YEAR_LENGTH,
                Date::INVALID_DATE,
            ],
            [
                'fields' => [
                    'writtenOutcome' => $writtenOutcomeValue,
                ],
            ]
        );

        // Context with validateIf(writtenOutcome=piwo_decision) should pass
        // With valid date not in future
        $this->assertFormElementValid(
            $element,
            [
                'year'  => date('Y') - 1,
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                'fields' => [
                    'writtenOutcome' => $writtenOutcomeValue,
                ],
            ]
        );
    }

    /**
     * @return array
     */
    public function dpTestDateFieldsWithComplexValidation()
    {
        return [
            ['tcWrittenDecisionDate', 'piwo_decision'],
            ['decisionLetterSentDate', 'piwo_decision'],
            ['tcWrittenReasonDate', 'piwo_reason'],
            ['writtenReasonLetterDate', 'piwo_reason'],
        ];
    }

    public function testWrittenDecisionLetterDate()
    {
        $element = ['fields', 'writtenDecisionLetterDate'];

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => 'XXX',
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                Date::INVALID_DATE,
            ]
        );
        $this->assertFormElementValid(
            $element,
            [
                'year'  => date('Y') - 1,
                'month' => date('m'),
                'day'   => date('j'),
            ]
        );
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
