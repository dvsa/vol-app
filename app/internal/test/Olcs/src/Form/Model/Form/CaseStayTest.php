<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class CaseStayTest
 *
 * @group FormTests
 */
class CaseStayTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\CaseStay::class;

    public function testStayType()
    {
        $element = ['fields', 'stayType'];
        $this->assertFormElementHidden($element);
    }

    public function testRequestDate()
    {
        $element = ['fields', 'requestDate'];
        $this->assertFormElementIsRequired($element, true);

        $this->assertFormElementValid(
            $element,
            [
                'year' => '2017',
                'month' => '10',
                'day' => '10'
            ]
        );
    }

    public function testDecisionDate()
    {
        $element = ['fields', 'decisionDate'];
        $this->assertFormElementIsRequired($element, false);

        $this->assertFormElementValid(
            $element,
            [
                'year' => '2017',
                'month' => '1',
                'day' => '1'
            ],
            [
                'fields' => [
                    'requestDate' => [
                        'year' => '2016',
                        'month' => '10',
                        'day' => '10'
                    ],
                ],
            ]
        );
    }

    public function testDvsaNotified()
    {
        $element = ['fields', 'dvsaNotified'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testIsWithdrawn()
    {
        $element = ['fields', 'isWithdrawn'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testWithdrawnDate()
    {
        $element = ['fields', 'withdrawnDate'];
        $this->assertFormElementIsRequired($element, false);

        $this->assertFormElementValid(
            $element,
            '2017-01-01',
            [
                'fields' => [
                    'isWithdrawn' => 'Y',
                ],
            ]
        );
    }

    public function testNotes()
    {
        $element = ['fields', 'notes'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementText($element, 5, 4000);
    }

    public function testOutcome()
    {
        $element = ['fields', 'outcome'];
        $this->assertFormElementDynamicSelect($element, true);
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

    public function testCase()
    {
        $element = ['fields', 'case'];
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
