<?php

namespace OlcsTest\Form\Model\Form;

use Laminas\Validator\NotEmpty;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class InspectionRequestTest
 *
 * @group FormTests
 */
class InspectionRequestTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\InspectionRequest::class;

    public function testId()
    {
        $this->assertFormElementHidden(['data', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['data', 'version']);
    }

    public function testReportType()
    {
        $this->assertFormElementDynamicSelect(['data', 'reportType'], true);
    }

    public function testOperatingCentre()
    {
        $this->assertFormElementDynamicSelect(
            ['data', 'operatingCentre'],
            false
        );
    }

    public function testInspectorName()
    {
        $this->assertFormElementRequired(
            ['data', 'inspectorName'],
            false
        );
    }

    public function testRequestType()
    {
        $this->assertFormElementDynamicSelect(['data', 'requestType'], false);
    }

    public function testRequestDate()
    {
        $element = ['data', 'requestDate'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementDate($element);
    }

    public function testDueDate()
    {
        $element = ['data', 'dueDate'];
        $this->assertFormElementIsRequired($element, true);
        $this->assertFormElementDate($element, [
            'data' => [
                'requestDate' => [
                    'year' => '1990',
                    'month' => '01',
                    'day' => '01',
                ],
            ]
        ]);
    }

    public function testReturnDate()
    {
        $element = ['data', 'returnDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testResultType()
    {
        $this->assertFormElementDynamicSelect(['data', 'resultType'], true);
    }

    public function testFromDate()
    {
        $element = ['data', 'fromDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testToDate()
    {
        $element = ['data', 'toDate'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementDate($element);
    }

    public function testVehiclesExaminedNo()
    {
        $this->assertFormElementRequired(['data', 'vehiclesExaminedNo'], false);
    }

    public function testTrailersExaminedNo()
    {
        $this->assertFormElementRequired(['data', 'trailersExaminedNo'], false);
    }

    public function testRequestorNotes()
    {
        $this->assertFormElementRequired(['data', 'requestorNotes'], false);
    }

    public function testInspectorNotes()
    {
        $this->assertFormElementRequired(['data', 'inspectorNotes'], false);
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
