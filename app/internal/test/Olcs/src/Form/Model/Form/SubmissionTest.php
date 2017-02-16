<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class SubmissionTest
 *
 * @group FormTests
 */
class SubmissionTest
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Submission::class;

    protected function setUp()
    {
        $this->markTestIncomplete(sprintf('"%s" form not tested', $this->formName));
    }
}
