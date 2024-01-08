<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class BusRegisterServiceTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class BusRegisterServiceTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\BusRegisterService::class;

    public function testGrantValidation()
    {
        $this->assertFormElementHtml(['grant', 'grantValidation']);
    }

    public function testTimetableAcceptable()
    {
        $element = ['timetable', 'timetableAcceptable'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testTimetableMapSupplied()
    {
        $element = ['timetable', 'mapSupplied'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testTimetableRouteDescription()
    {
        $element = ['timetable', 'routeDescription'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 1000);
    }

    public function testConditionsTable()
    {
        $element = ['conditions', 'table', 'table'];
        $this->assertFormElementTable($element);
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);

        $element = ['conditions', 'table', 'action'];
        $this->assertFormElementHidden($element);

        $element = ['conditions', 'table', 'rows'];
        $this->assertFormElementHidden($element);

        $element = ['conditions', 'table', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testConditionsTrcChecked()
    {
        $element = ['conditions', 'trcConditionChecked'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testConditionsTrcNotes()
    {
        $element = ['conditions', 'trcNotes'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testCopiedToLaPte()
    {
        $element = ['fields', 'copiedToLaPte'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testLaShortNote()
    {
        $element = ['fields', 'laShortNote'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testOpNotifiedLaPte()
    {
        $element = ['fields', 'opNotifiedLaPte'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testApplicationSigned()
    {
        $element = ['fields', 'applicationSigned'];
        $this->assertFormElementType($element, Radio::class);
        $this->assertFormElementRequired($element, true);
    }

    public function testVariationsReasonHtml()
    {
        $this->assertFormElementHtml(['fields', 'variationReasonsHtml']);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['fields', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['fields', 'version']);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
