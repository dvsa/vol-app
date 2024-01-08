<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Validator\InArray;

/**
 * Class PrinterExceptionTest
 *
 * @group FormTests
 */
class PrinterExceptionTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Admin\Form\Model\Form\PrinterException::class;

    public function testVersion()
    {
        $element = ['version'];
        $this->assertFormElementHidden($element);
    }

    public function testId()
    {
        $element = ['id'];
        $this->assertFormElementHidden($element);
    }

    public function testExceptionTeam()
    {
        $element = ['exception-details', 'team'];
        $this->assertFormElementHidden($element);
    }

    public function testExceptionTeamId()
    {
        $element = ['exception-details', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testExceptionTeamVersion()
    {
        $element = ['exception-details', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testExceptionTeamOrUser()
    {
        $element = ['exception-details', 'teamOrUser'];
        $this->assertFormElementValid($element, 'user');
        $this->assertFormElementValid($element, 'team');
        $this->assertFormElementNotValid($element, 'ABC', [InArray::NOT_IN_ARRAY]);
    }

    public function testTeamPrinterCategoryTeam()
    {
        $element = ['team-printer', 'categoryTeam'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testTeamPrinterSubCategoryTeam()
    {
        $element = ['team-printer', 'subCategoryTeam'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testTeamPrinter()
    {
        $element = ['team-printer', 'printer'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUserPrinterSelect()
    {
        $element = ['user-printer', 'user'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUserPrinterCategoryUser()
    {
        $element = ['user-printer', 'categoryUser'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUserPrinterSubCategoryUser()
    {
        $element = ['user-printer', 'subCategoryUser'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testUserPrinter()
    {
        $element = ['user-printer', 'printer'];
        $this->assertFormElementDynamicSelect($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testAddAnother()
    {
        $element = ['form-actions', 'addAnother'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
