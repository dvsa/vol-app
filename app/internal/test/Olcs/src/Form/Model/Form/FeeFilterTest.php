<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

/**
 * Class FeeFilterTest
 *
 * @group FormTests
 */
class FeeFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\FeeFilter::class;

    public function testStatus()
    {
        $element = ['status'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'current');
        $this->assertFormElementValid($element, 'historical');
        $this->assertFormElementValid($element, 'all');
    }

    public function testFilter()
    {
        $this->assertFormElementActionButton(
            ['filter']
        );
    }
}
