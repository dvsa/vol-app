<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class VariationApproveSchedule41Test
 *
 * @group FormTests
 */
class VariationApproveSchedule41Test extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\VariationApproveSchedule41::class;

    public function testIsTrueS4()
    {
        $this->assertFormElementRequired(['isTrueS4'], true);
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
