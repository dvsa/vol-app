<?php

declare(strict_types=1);

namespace OlcsTest\Form\Model\Form;

use Olcs\Form\Model\Form\BusServiceEndDate;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * @see BusServiceEndDate
 * @group FormTests
 */
class BusServiceEndDateTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = BusServiceEndDate::class;

    public function testEndDate(): void
    {
        $element = ['fields', 'endDate'];
        $this->assertFormElementIsRequired($element);
        $this->assertFormElementDate($element);
    }

    public function testId(): void
    {
        $element = ['fields', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion(): void
    {
        $element = ['fields', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit(): void
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel(): void
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
