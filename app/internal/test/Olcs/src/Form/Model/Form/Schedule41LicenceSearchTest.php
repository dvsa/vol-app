<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class Schedule41LicenceSearchTest
 *
 * @group FormTests
 */
class Schedule41LicenceSearchTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Schedule41LicenceSearch::class;

    public function testLicenceNumber()
    {
        $element = ['licence-number', 'licenceNumber'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementText($element, 2, 35);
    }

    public function testConfirm()
    {
        $this->assertFormElementActionButton(['form-actions', 'confirm']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
