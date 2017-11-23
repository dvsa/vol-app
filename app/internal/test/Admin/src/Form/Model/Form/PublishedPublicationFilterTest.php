<?php

namespace AdminTest\Form\Model\Form;

use Admin\Form\Model\Form\PublishedPublicationFilter;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Zend\Form\Element\Select;

class PublishedPublicationFilterTest extends AbstractFormValidationTestCase
{
    protected $formName = PublishedPublicationFilter::class;

    public function testPubTypeSelect()
    {
        $this->assertFormElementType(['pubType'], Select::class);
    }

    public function testPubTypeAllowedValues()
    {
        $this->assertFormElementValid(['pubType'], 'A&D');
        $this->assertFormElementValid(['pubType'], 'N&P');
    }

    public function testPubTypeEmptyValue()
    {
        $this->assertFormElementValid(['pubType'], '');
    }

    public function testPubTypeDisallowed()
    {
        $this->assertFormElementNotValid(['pubType'], 'Something Else', 'notInArray');
    }

    public function testFilterButton()
    {
        $this->assertFormElementActionButton(['filter']);
    }
}
