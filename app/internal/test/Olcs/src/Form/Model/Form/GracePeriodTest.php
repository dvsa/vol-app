<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\GreaterThan;

/**
 * Class GracePeriodTest
 *
 * @group FormTests
 */
class GracePeriodTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\GracePeriod::class;

    public function testStartDate()
    {
        $this->assertFormElementDate(['details', 'startDate']);
    }

    public function testEndDate()
    {
        $element = ['details', 'endDate'];

        $this->assertFormElementValid(
            $element,
            ['day' => 10, 'month' => 2, 'year' => 2016],
            [
                'details' =>
                    [
                        'startDate' => [
                            'day'   => 5,
                            'month' => 1,
                            'year'  => 2016,
                        ],
                    ],
            ]
        );

        $this->assertFormElementNotValid(
            $element,
            ['day' => 1, 'month' => 2, 'year' => 2015],
            [GreaterThan::NOT_GREATER],
            [
                'details' =>
                    [
                        'startDate' => [
                            'day'   => 5,
                            'month' => 1,
                            'year'  => 2016,
                        ],
                    ],
            ]
        );
    }

    public function testDescription()
    {
        $element = ['details', 'description'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 1, 90);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(['form-actions', 'addAnother']);
    }
}
