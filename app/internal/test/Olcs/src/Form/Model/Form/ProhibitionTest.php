<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Validator\DateCompare;

/**
 * Class ProhibitionTest
 *
 * @group FormTests
 */
class ProhibitionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Prohibition::class;

    public function testProhibitionDate()
    {
        $element = ['fields', 'prohibitionDate'];
        $this->assertFormElementDate($element);
    }

    public function testVrm()
    {
        $element = ['fields', 'vrm'];
        $this->assertFormElementIsRequired($element, false);
    }

    public function testIsTrailer()
    {
        $element = ['fields', 'isTrailer'];
        $this->assertFormElementType($element, OlcsCheckbox::class);
        $this->assertFormElementIsRequired($element, true);
    }

    public function testProhibitionType()
    {
        $this->assertFormElementDynamicSelect(
            ['fields', 'prohibitionType'],
            true
        );
    }

    public function testClearedDate()
    {
        $element = ['fields', 'clearedDate'];

        // Element is not required
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        // Cannot be todays date
        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => date('Y'),
                'month' => date('m'),
                'day'   => date('j'),
            ],
            ['invalidField']
        );

        // This date cannot be in the future
        $this->assertFormElementNotValid(
            $element,
            [
                'year'  => date('Y'),
                'month' => date('m'),
                'day'   => date('j'),
            ],
            [
                'invalidField',
                DateCompare::NOT_GTE,
            ],
            [
                'fields' => [
                    'prohibitionDate' => [
                        'year'  => date('Y') + 1,
                        'month' => date('m'),
                        'day'   => date('j'),
                    ],
                ],
            ]
        );
    }

    public function testImposedAt()
    {
        $this->assertFormElementIsRequired(['fields', 'imposedAt'], false);
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
