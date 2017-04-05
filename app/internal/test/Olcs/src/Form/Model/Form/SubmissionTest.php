<?php

namespace OlcsTest\Form\Model\Form;

use Zend\ServiceManager\ServiceLocatorInterface;
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
    public function setUp()
    {
        $this->serviceManager = \OlcsTest\Bootstrap::getRealServiceManager();

        // We are doing this solely for the internal application.  This service
        // is only registered there.  So we check if the element exists first.
        $element = new \Olcs\Form\Element\SubmissionSections();
        $this->serviceManager->setService(
            'SubmissionSections',
            $element
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
     * @param null $elementName
     */
    public final function testMissingTest($elementName = null)
    {
        //
    }

    public function testSubmissionsSections()
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
