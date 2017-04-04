<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class SubmissionTest
 *
 * @group FormTests
 */
class SubmissionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Submission::class;

    /**
     * @internal Skipping 'submissionSections' because the element is mainly
     * powered by Javascript.  It's dynamic and also dynamically populated.
     * See this link for example: http://olcs-internal.olcs.gov.uk/case/28/submission/add/
     */
    public function testSubmissionSections()
    {
        $element = ['fields', 'submissionSections'];
        self::$testedElements[implode($element, '.')] = true;
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testCase()
    {
        $this->assertFormElementHidden(['fields', 'case']);
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
