<?php

namespace OlcsTest\Form\Model\Form;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
    public function setUp(): void
    {
        $this->getServiceManager()->get('FormElementManager')->setFactory(
            'SubmissionSections',
            function () {
                return new \Olcs\Form\Element\SubmissionSections();
            }
        );

        parent::setUp();
    }

    /**
     * @internal This is not a test but an override for the method in the abstract
     * class.  The parent setUp uses a method to getForm.  The form uses the services
     * already pre-defined.  When using setUp in this test, it does not take priority.
     * Other methods have been used such as: assertPreConditions and setUpBeforeClass
     * with no difference.  So we have this method to override the missingTest function.
     * Elements are still tested.
     *
     * @doesNotPerformAssertions
     */
    final public function testMissingTest($element = null)
    {
        // Violation if unused element, but parent has $element.
        unset($element);
    }

    public function testSubmissionsSections()
    {
        $element = ['fields', 'submissionSections'];
        $this->assertElementExists($element);
        $this->assertFormElementType($element, \Olcs\Form\Element\SubmissionSections::class);
        self::$testedElements[implode('.', $element)] = true;
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
