<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class CpidFilterTest
 *
 * @group FormTests
 */
class CpidFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\CpidFilter::class;

    public function testFilterDate()
    {
        $element = ['status'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testFilterButton()
    {
        $element = ['filter'];
        $this->assertFormElementActionButton($element);
    }
}
