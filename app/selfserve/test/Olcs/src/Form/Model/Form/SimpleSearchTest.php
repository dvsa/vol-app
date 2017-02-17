<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class SimpleSearchTest
 *
 * @group FormTests
 */
class SimpleSearchTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SimpleSearch::class;

    public function testSearch()
    {
        $element = ['search'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element);
    }

    public function testSubmit()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testIndex()
    {
        $element = ['index'];
        $this->assertFormElementHidden($element);
    }
}
