<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class PublicationNotNewTest
 * All fields in this test are read-only.  No fields are required.
 *
 * @group FormTests
 */
class PublicationNotNewTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\PublicationNotNew::class;

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
        $this->assertFormElementRequired(['readOnlyText', 'text1'], false);
    }

    public function testText2()
    {
        $this->assertFormElementRequired(['readOnlyText', 'text2'], false);
    }

    public function testText3()
    {
        $this->assertFormElementRequired(['readOnlyText', 'text3'], false);
    }

    public function testTextId()
    {
        $this->assertFormElementRequired(['readOnlyText', 'id'], false);
    }

    public function testTextVersion()
    {
        $this->assertFormElementRequired(['readOnlyText', 'version'], false);
    }
}
