<?php

namespace AdminTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * @covers \Admin\Form\Model\Form\CasesOpenReportFilter
 * @group  FormTests
 */
class CasesOpenReportFilterTest extends AbstractFormValidationTestCase
{
    protected $formName = \Admin\Form\Model\Form\CasesOpenReportFilter::class;

    public function testTrafficAreas()
    {
        $this->assertFormElementDynamicMultiCheckbox(['trafficAreas']);
    }

    public function testCaseType()
    {
        $element = ['caseType'];
        $this->assertFormElementDynamicSelect($element);
        $this->assertFormElementAllowEmpty($element, false);
    }

    public function testLicenceStatus()
    {
        $element = ['licenceStatus'];
        $this->assertFormElementDynamicSelect($element);
        $this->assertFormElementAllowEmpty($element, false);
    }

    public function testApplicationStatus()
    {
        $element = ['applicationStatus'];
        $this->assertFormElementDynamicSelect($element);
        $this->assertFormElementAllowEmpty($element, false);
    }

    public function testFilterButton()
    {
        $element = ['filter'];
        $this->assertFormElementActionButton($element);
    }

    public function testLimit()
    {
        $element = ['limit'];
        $this->assertFormElementHidden($element);
    }
}
