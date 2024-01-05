<?php

namespace OlcsTest\Form\Model\Form;

use OlcsTest\TestHelpers\AbstractFormValidationTestCase;

/**
 * Class TransportManagerApplicationResendTest
 *
 * @group FormTests
 */
class TransportManagerApplicationResendTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TransportManagerApplicationResend::class;

    public function testSubmit()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
    }
}
