<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class SearchOperatorTest
 *
 * @group FormTests
 */
class SearchOperatorTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\SearchOperator::class;

    public function testSearchBy()
    {
        $element = ['searchBy'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'address');
        $this->assertFormElementValid($element, 'business');
        $this->assertFormElementValid($element, 'licence');
        $this->assertFormElementValid($element, 'person');
    }

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
