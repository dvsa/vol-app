<?php

namespace AdminTest\Form\Model\Form;

use Admin\Form\Model\Form\PublishedPublicationFilter;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Select;

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

    public function testPublicationDate()
    {
        $this->assertFormElementMonthSelect(['pubDate']);
    }

    public function testTrafficArea()
    {
        $this->assertFormElementDynamicSelect(['trafficArea'], false);
    }

    public function testFilterButton()
    {
        $this->assertFormElementActionButton(['filter']);
    }
}
