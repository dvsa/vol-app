<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\InputFilter\Date;
use Laminas\Form\Element\Select;

/**
 * Class CpmsReportTest
 *
 * @group FormTests
 */
class CpmsReportTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\CpmsReport::class;

    public function testReportOptionsCode()
    {
        $element = ['reportOptions', 'reportCode'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testStartDate()
    {
        $element = ['reportOptions', 'startDate'];

        $pastYear = date('Y') - 1;

        $errorMessages = [
            'inFuture',
        ];

        $this->assertFormElementValid(
            $element,
            ['day' => 1, 'month' => '2', 'year' => $pastYear]
        );

        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => '1', 'year' => $pastYear + 2],
            $errorMessages
        );
    }

    public function testEndDate()
    {
        $pastYear = date('Y') - 2;

        $element = ['reportOptions', 'endDate'];

        $errorMessages = [
            'invalidField',
        ];

        $this->assertFormElementNotValid(
            $element,
            ['day' => '1', 'month' => '1', 'year' => $pastYear + 2],
            $errorMessages
        );
    }

    public function testGenerate()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'generate']
        );
    }
}
