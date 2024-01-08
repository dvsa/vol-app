<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Radio;

/**
 * Class TaskAllocationRuleTest
 *
 * @group FormTests
 */
class TaskAllocationRuleTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\TaskAllocationRule::class;

    public function testId()
    {
        $this->assertFormElementHidden(['id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['version']);
    }

    public function testTableTable()
    {
        $element = ['details', 'taskAlphaSplit', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);
    }

    public function testTableAction()
    {
        $this->assertFormElementNoRender(
            ['details', 'taskAlphaSplit', 'action']
        );
    }

    public function testTableRows()
    {
        $this->assertFormElementHidden(
            ['details', 'taskAlphaSplit', 'rows']
        );
    }

    public function testTableId()
    {
        $this->assertFormElementNoRender(
            ['details', 'taskAlphaSplit', 'id']
        );
    }

    public function testCategory()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'category'],
            true
        );
    }

    public function testGoodsOrPsv()
    {
        $element = ['details', 'goodsOrPsv'];

        $this->assertFormElementType($element, Radio::class);

        $this->assertFormElementRequired($element, true);

        $this->assertFormElementValid($element, 'lcat_gv');
        $this->assertFormElementValid($element, 'lcat_psv');
        $this->assertFormElementValid($element, 'na');
    }

    public function testIsMlh()
    {
        $element = ['details', 'isMlh'];

        $this->assertFormElementType($element, Radio::class);

        $this->assertFormElementRequired($element, false);

        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testTrafficArea()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'trafficArea'],
            false
        );
    }

    public function testTeamId()
    {
        $this->assertFormElementHidden(
            ['details', 'teamId']
        );
    }

    public function testTeamSelect()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'team'],
            false
        );
    }

    public function testUserSelect()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'user'],
            false
        );
    }

    public function testDetailsFormId()
    {
        $this->assertFormElementHidden(['details', 'id']);
    }

    public function testDetailsFormVersion()
    {
        $this->assertFormElementHidden(['details', 'version']);
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
