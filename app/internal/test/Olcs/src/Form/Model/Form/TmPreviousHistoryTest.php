<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class TmPreviousHistoryTest
 *
 * @group FormTests
 */
class TmPreviousHistoryTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TmPreviousHistory::class;

    public function testPreviousHistoryTable()
    {
        $element = ['previousHistory', 'convictions', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testPreviousHistoryAction()
    {
        $this->assertFormElementNoRender(
            ['previousHistory', 'convictions', 'action']
        );
    }

    public function testPreviousHistoryRows()
    {
        $this->assertFormElementHidden(
            ['previousHistory', 'convictions', 'rows']
        );
    }

    public function testPreviousHistoryTableId()
    {
        $this->assertFormElementNoRender(
            ['previousHistory', 'convictions', 'id']
        );
    }

    public function testPreviousLicencesTable()
    {
        $element = ['previousHistory', 'previousLicences', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testPreviousLicencesAction()
    {
        $this->assertFormElementNoRender(
            ['previousHistory', 'previousLicences', 'action']
        );
    }

    public function testPreviousLicencesRows()
    {
        $this->assertFormElementHidden(
            ['previousHistory', 'previousLicences', 'rows']
        );
    }

    public function testPreviousLicencesTableId()
    {
        $this->assertFormElementNoRender(
            ['previousHistory', 'previousLicences', 'id']
        );
    }
}
