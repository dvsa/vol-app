<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Form\Element\Select;

/**
 * Class LicenceOverviewTest
 *
 * @group FormTests
 */
class LicenceOverviewTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\LicenceOverview::class;

    public function testContinuationDate()
    {
        $this->assertFormElementDate(['details', 'continuationDate']);
    }

    public function testReviewDate()
    {
        $this->assertFormElementDate(['details', 'reviewDate']);
    }

    public function testLeadTcArea()
    {
        $element = ['details', 'leadTcArea'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, false);
    }

    public function testTranslateToWelsh()
    {
        $element = ['details', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
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
