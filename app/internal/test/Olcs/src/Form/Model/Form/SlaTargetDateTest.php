<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\Date;
use Common\Validator\Date as CommonDateValidator;
use Laminas\Validator\GreaterThan;

/**
 * Class SlaTargetDateTest
 *
 * @group FormTests
 */
class SlaTargetDateTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SlaTargetDate::class;

    public function testEntityTypeHtml()
    {
        $this->assertFormElementHtml(['fields', 'entityTypeHtml']);
    }

    public function testEntityId()
    {
        $this->assertFormElementHidden(['fields', 'entityId']);
    }

    public function testEntityType()
    {
        $this->assertFormElementHidden(['fields', 'entityType']);
    }

    public function testAgreedDate()
    {
        $this->assertFormElementDate(['fields', 'agreedDate']);
    }

    public function testSentDate()
    {
        $element = ['fields', 'sentDate'];

        // Invalid date format and no field
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
                'invalidField',
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year'  => date('Y') - 1,
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                'fields' => [
                    'agreedDate' => [
                        'year'  => date('Y') - 2,
                        'month' => date('m'),
                        'day'   => date('j'),
                    ],
                ],
            ]
        );
    }

    public function testTargetDate()
    {
        $element = ['fields', 'targetDate'];

        // Invalid date format and no field
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
                'invalidField',
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year'  => date('Y') - 1,
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                'fields' => [
                    'agreedDate' => [
                        'year'  => date('Y') - 2,
                        'month' => date('m'),
                        'day'   => date('j'),
                    ],
                ],
            ]
        );
    }

    public function testUnderDelegation()
    {
        $this->assertFormElementRequired(['fields', 'underDelegation'], true);
    }

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
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
