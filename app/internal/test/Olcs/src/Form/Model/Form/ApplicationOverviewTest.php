<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Select;
use Common\Form\Elements\Custom\OlcsCheckbox;

/**
 * Class ApplicationOverviewTest
 *
 * @group FormTests
 */
class ApplicationOverviewTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\ApplicationOverview::class;

    public function testId()
    {
        $element = ['details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['details', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testTrackingId()
    {
        $element = ['tracking', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testTrackingVersion()
    {
        $element = ['tracking', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testLeadTcArea()
    {
        $element = ['details', 'leadTcArea'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementRequired($element, false);
    }

    public function testReceivedDate()
    {
        $element = ['details', 'receivedDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testCompletionDate()
    {
        $element = ['details', 'targetCompletionDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['details', 'translateToWelsh'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testOverrideOppositionDate()
    {
        $element = ['details', 'overrideOppositionDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testSave()
    {
        $element = ['form-actions', 'save'];
        $this->assertFormElementActionButton($element);
    }

    public function testSaveAndContinue()
    {
        $element = ['form-actions', 'saveAndContinue'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
