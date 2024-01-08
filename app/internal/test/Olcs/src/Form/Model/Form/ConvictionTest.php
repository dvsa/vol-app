<?php

namespace OlcsTest\Form\Model\Form;

use Common\Form\Elements\Validators\Date;
use Common\Form\Elements\Validators\DateNotInFuture;
use Common\Validator\Date as DateValidator;
use Common\Validator\DateCompare;
use DateTime;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\StringLength;
use Laminas\Form\Element\Select;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class ConvictionTest
 *
 * @group FormTests
 */
class ConvictionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Conviction::class;

    public function testDefendantType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'defendantType'],
            true
        );
    }

    public function testPersonFirstname()
    {
        $this->assertFormElementText(['fields', 'personFirstname']);
    }

    public function testPersonLastname()
    {
        $this->assertFormElementText(['fields', 'personLastname']);
    }

    public function testDateOfBirth()
    {
        $element = ['fields', 'birthDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        $futureDate = $this->createFutureDate();

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => $futureDate->format('Y'),
                'month' => $futureDate->format('m'),
                'day'   => $futureDate->format('d'),
            ],
            [DateNotInFuture::IN_FUTURE],
            [
                'fields' => [
                    'defendantType' => ['def_t_op' => true],
                ],
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => '2017',
                'month' => '10',
                'day'   => 'XXX',
            ],
            [
                DateValidator::DATE_ERR_CONTAINS_STRING,
                Date::INVALID_DATE,
            ],
            [
                'fields' => [
                    'defendantType' => ['def_t_op' => true],
                ],
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year'  => '1987',
                'month' => '06',
                'day'   => '15',
            ],
            [
                'fields' => [
                    'defendantType' => ['def_t_op' => true],
                ],
            ]
        );
    }

    public function testConvictionCategory()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'convictionCategory'],
            false
        );
    }

    public function testCategoryText()
    {
        $element = ['fields', 'categoryText'];

        $this->assertFormElementNotValid(
            $element,
            "",
            [StringLength::TOO_SHORT],
            [
                'fields' => [
                    'convictionCategory' => '',
                ],
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            "abc",
            [StringLength::TOO_SHORT],
            [
                'fields' => [
                    'convictionCategory' => '',
                ],
            ]
        );

        $this->assertFormElementValid(
            $element,
            "abc123",
            [
                'fields' => [
                    'convictionCategory' => '',
                ],
            ]
        );
    }

    public function testOffenceDate()
    {
        $element = ['fields', 'offenceDate'];
        $this->assertFormElementIsRequired(
            $element,
            true,
            [DateCompare::INVALID_FIELD]
        );

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => 'XXXX',
                'month' => '10',
                'day'   => '10',
            ],
            [
                DateValidator::DATE_ERR_CONTAINS_STRING,
                DateValidator::DATE_ERR_YEAR_LENGTH,
                Date::INVALID_DATE,
                DateCompare::INVALID_FIELD,
                DateCompare::NO_COMPARE,
            ],
            [
                'fields' => [
                    'convictionDate' => [
                        'year'  => '2017',
                        'month' => '10',
                        'day'   => '5',
                    ],
                ],
            ]
        );

        $futureDate = $this->createFutureDate();

        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => $futureDate->format('Y'),
                'month' => $futureDate->format('m'),
                'day'   => $futureDate->format('d'),
            ],
            [
                DateNotInFuture::IN_FUTURE,
                DateCompare::INVALID_FIELD,
                DateCompare::NO_COMPARE,
                DateCompare::NOT_LTE,
            ],
            [
                'fields' => [
                    'convictionDate' => [
                        'year'  => '2017',
                        'month' => '10',
                        'day'   => '5',
                    ],
                ],
            ]
        );

        $this->assertFormElementValid(
            $element,
            [
                'year'  => '2016',
                'month' => '10',
                'day'   => '10',
            ],
            [
                'fields' => [
                    'convictionDate' => [
                        'year'  => '2016',
                        'month' => '10',
                        'day'   => '10',
                    ],
                ],
            ]
        );
    }

    public function testConvictionDate()
    {
        $this->assertFormElementDate(['fields', 'convictionDate']);
    }

    public function testMsi()
    {
        $element = ['fields', 'msi'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, Select::class);
    }

    public function testCourt()
    {
        $element = ['fields', 'court'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 2, 70);
    }

    public function testPenalty()
    {
        $element = ['fields', 'penalty'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testCosts()
    {
        $element = ['fields', 'costs'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 2, 255);
    }

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testTakenIntoConsideration()
    {
        $element = ['fields', 'takenIntoConsideration'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testIsDeclared()
    {
        $element = ['fields', 'isDeclared'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, Select::class);
    }

    public function testIsDealtWith()
    {
        $element = ['fields', 'isDealtWith'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
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

    /**
     * Create a date in the future
     *
     * @return DateTime 26 hours ahead of current time to allow for slow tests and DST
     */
    private function createFutureDate()
    {
        return new DateTime('+26 hours');
    }
}
