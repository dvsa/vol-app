<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\InArray;

/**
 * Class SystemInfoMessageTest
 *
 * @group FormTests
 */
class SystemInfoMessageTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\SystemInfoMessage::class;

    public function testDetailsId()
    {
        $element = ['details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testIsInternal()
    {
        $element = ['details', 'isInternal'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid($element, 'C', [InArray::NOT_IN_ARRAY]);
    }

    public function testDescription()
    {
        $element = ['details', 'description'];
        $this->assertFormElementRequired($element, true);
    }

    public function testStartDate()
    {
        $element = [ 'details', 'startDate' ];
        $this->assertFormElementDateTime($element, true);
    }

    public function testEndDate()
    {
        $element = [ 'details', 'endDate' ];

        $this->assertFormElementDateTimeNotValidCheck($element);
        $this->assertFormElementDateTimeValidCheck(
            $element,
            null,
            [
                'details' => [
                    'startDate' => [
                        'year' => date('y') + 1,
                        'month' => '10',
                        'day' => '01',
                        'hour' => '21',
                        'minute' => '30',
                        'seconds' => '10',
                    ]
                ]
            ]
        );
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancelButton()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
