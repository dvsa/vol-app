<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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
        $this->assertFormElementHidden(['index']);
    }

    public function testSearch()
    {
        $this->assertFormElementHidden(['search']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['submit']);
    }
}
