<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;
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
        $this->assertFormElementHidden(['details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['details', 'version']);
    }

    public function testTrackingId()
    {
        $this->assertFormElementHidden(['tracking', 'id']);
    }

    public function testTrackingVersion()
    {
        $this->assertFormElementHidden(['tracking', 'version']);
    }

    public function testLeadTcArea()
    {
        $element = ['details', 'leadTcArea'];
        $this->assertFormElementType($element, Select::class);
        $this->assertFormElementIsRequired($element, false);
    }

    public function testReceivedDate()
    {
        $element = ['details', 'receivedDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testCompletionDate()
    {
        $element = ['details', 'targetCompletionDate'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDate($element);
    }

    public function testTranslateToWelsh()
    {
        $element = ['details', 'translateToWelsh'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testOverrideOppositionDate()
    {
        $element = ['details', 'overrideOppositionDate'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testSave()
    {
        $this->assertFormElementActionButton(['form-actions', 'save']);
    }

    public function testSaveAndContinue()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'saveAndContinue']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
