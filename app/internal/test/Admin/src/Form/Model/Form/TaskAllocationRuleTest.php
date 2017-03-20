<?php

namespace AdminTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Radio;

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
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
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
        $element = ['details', 'taskAlphaSplit', 'action'];
        $this->assertFormElementNoRender($element);
    }

    public function testTableRows()
    {
        $element = ['details', 'taskAlphaSplit', 'rows'];
        $this->assertFormElementHidden($element);
    }

    public function testTableId()
    {
        $element = ['details', 'taskAlphaSplit', 'id'];
        $this->assertFormElementNoRender($element);
    }

    public function testCategory()
    {
        $element = ['details', 'category'];
        $this->assertFormElementDynamicSelect($element, true);
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

    public function testGIsMlh()
    {
        $element = ['details', 'isMlh'];

        $this->assertFormElementType($element, Radio::class);

        $this->assertFormElementRequired($element, false);

        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testTrafficArea()
    {
        $element = ['details', 'trafficArea'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testTeamId()
    {
        $element = ['details', 'teamId'];
        $this->assertFormElementHidden($element);
    }

    public function testTeamSelect()
    {
        $element = ['details', 'team'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testUserSelect()
    {
        $element = ['details', 'user'];
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testDetailsFormId()
    {
        $element = ['details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testDetailsFormVersion()
    {
        $element = ['details', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
