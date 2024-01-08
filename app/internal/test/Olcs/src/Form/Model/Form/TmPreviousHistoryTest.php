<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

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

    public function testPreviousHistoryHasConvictions()
    {
        $element = ['previousHistory', 'hasConvictions'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid($element, 'Y', 'error');

        $this->assertFormElementValid(
            ['previousHistory'],
            [
                'hasConvictions' => 'Y',
                'convictions' => ['rows' => 1],
                'hasPreviousLicences' => 'N',
            ]
        );
        $this->assertFormElementNotValid(
            ['previousHistory'],
            [
                'hasConvictions' => 'Y',
                'convictions' => ['rows' => 0],
                'hasPreviousLicences' => 'N',
            ],
            'hasConvictions'
        );
    }

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

    public function testPreviousHistoryHasPreviousLicences()
    {
        $element = ['previousHistory', 'hasPreviousLicences'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'N');
        $this->assertFormElementNotValid($element, 'Y', 'error');

        $this->assertFormElementValid(
            ['previousHistory'],
            [
                'hasConvictions' => 'N',
                'hasPreviousLicences' => 'Y',
                'previousLicences' => ['rows' => 1],
            ]
        );
        $this->assertFormElementNotValid(
            ['previousHistory'],
            [
                'hasConvictions' => 'N',
                'hasPreviousLicences' => 'Y',
                'previousLicences' => ['rows' => 0],
            ],
            ['hasConvictions', 'hasPreviousLicences']
        );
        $this->assertFormElementNotValid(
            ['previousHistory'],
            [
                'hasConvictions' => 'Y',
                'convictions' => ['rows' => 1],
                'hasPreviousLicences' => 'Y',
                'previousLicences' => ['rows' => 0],
            ],
            ['hasConvictions', 'hasPreviousLicences']
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
