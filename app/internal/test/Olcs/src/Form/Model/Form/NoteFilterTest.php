<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class NoteFilterTest
 *
 * @group FormTests
 */
class NoteFilterTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\NoteFilter::class;

    public function testNoteType()
    {
        $this->assertFormElementDynamicSelect(['noteType'], false);
    }

    public function testSort()
    {
        $this->assertFormElementHidden(['sort']);
    }

    public function testOrder()
    {
        $this->assertFormElementHidden(['order']);
    }

    public function testLimit()
    {
        $this->assertFormElementHidden(['limit']);
    }

    public function testPage()
    {
        $this->assertFormElementHidden(['page']);
    }
}
