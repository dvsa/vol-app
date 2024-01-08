<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Validator\DateCompare;
use Laminas\Form\Element\Radio;

/**
 * Class TmCaseUnfitTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class TmCaseUnfitTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\TmCaseUnfit::class;

    public function testIsMsi()
    {
        $element = ['fields', 'isMsi'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testDecisionDate()
    {
        $this->assertFormElementDate(['fields', 'decisionDate']);
    }

    public function testNotifiedDate()
    {
        $element = ['fields', 'notifiedDate'];

        $this->assertFormElementValid(
            $element,
            ['day' => 10, 'month' => 2, 'year' => 2016],
            [
                'fields' =>
                    [
                        'decisionDate' => [
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
            [DateCompare::NOT_GTE],
            [
                'fields' =>
                    [
                        'decisionDate' => [
                            'day'   => 5,
                            'month' => 1,
                            'year'  => 2016,
                        ],
                    ],
            ]
        );
    }

    public function testUnfitnessStartDate()
    {
        $this->assertFormElementDate(['fields', 'unfitnessStartDate']);
    }

    public function testUnfitnessEndDate()
    {
        $element = ['fields', 'unfitnessEndDate'];

        $this->assertFormElementValid(
            $element,
            ['day' => 10, 'month' => 2, 'year' => 2016],
            [
                'fields' =>
                    [
                        'unfitnessStartDate' => [
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
            [DateCompare::NOT_GTE],
            [
                'fields' =>
                    [
                        'unfitnessStartDate' => [
                            'day'   => 5,
                            'month' => 1,
                            'year'  => 2016,
                        ],
                    ],
            ]
        );
    }

    public function testUnfitnessReasons()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'unfitnessReasons'],
            true
        );
    }

    public function testRehabMeasures()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'rehabMeasures'],
            true
        );
    }

    public function testDecision()
    {
        $this->assertFormElementHidden(['fields', 'decision']);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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
