<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PublicationTest
 * @package OlcsTest\FormTest
 * @group FormTests
 */
class PublicationTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\Publication::class;

    public function testPublicationNumber()
    {
        $this->assertFormElementRequired(['readOnly', 'publicationNo'], false);
    }

    public function testStatus()
    {
        $this->assertFormElementRequired(['readOnly', 'status'], false);
    }

    public function testTypeArea()
    {
        $this->assertFormElementRequired(['readOnly', 'typeArea'], false);
    }

    public function testPublicationDate()
    {
        $this->assertFormElementRequired(
            ['readOnly', 'publicationDate'],
            false
        );
    }

    public function testSection()
    {
        $this->assertFormElementRequired(['readOnly', 'section'], false);
    }

    public function testTrafficArea()
    {
        $this->assertFormElementRequired(['readOnly', 'trafficArea'], false);
    }

    public function testText1()
    {
        $this->assertFormElementRequired(['fields', 'text1'], false);
    }

    public function testText2()
    {
        $this->assertFormElementRequired(['fields', 'text2'], false);
    }

    public function testText3()
    {
        $this->assertFormElementRequired(['fields', 'text3'], false);
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
