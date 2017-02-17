<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class SearchFilterTest
 *
 * @group FormTests
 */
class SearchFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SearchFilter::class;

    public function testIndex()
    {
        $element = ['index'];
        $this->assertFormElementHidden($element);
    }

    public function testText()
    {
        $element = ['text', 'search'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
    }

    public function testSearch()
    {
        $element = ['search'];
        $this->assertFormElementHidden($element);
    }

    public function testSearchBy()
    {
        $element = ['searchBy'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
    }
}
