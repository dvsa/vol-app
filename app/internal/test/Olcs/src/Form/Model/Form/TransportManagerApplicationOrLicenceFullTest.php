<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;
use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\Types\AttachFilesButton;

/**
 * Class TransportManagerApplicationOrLicenceFullTest
 *
 * @group FormTests
 */
class TransportManagerApplicationOrLicenceFullTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\TransportManagerApplicationOrLicenceFull::class;

    public function testResponsibilityHoursOfWeek()
    {
        $weekdaysElements = ['hoursMon', 'hoursTue', 'hoursWed', 'hoursThu', 'hoursFri', 'hoursSat', 'hoursSun'];

        $weekdaysElmsPath = [
            'details',
            'hoursOfWeek',
            'hoursPerWeekContent',
        ];
        foreach ($weekdaysElements as $day) {
            $element = $weekdaysElmsPath;
            array_push($element, $day);

            $this->assertFormElementAllowEmpty($element, true);
            $this->assertFormElementNotValid(
                $element,
                'abc',
                [
                    \Laminas\I18n\Validator\IsFloat::NOT_FLOAT,
                    \Laminas\Validator\Between::VALUE_NOT_NUMERIC,
                ]
            );

            $this->assertFormElementValid($element, 1.1);
        }
    }

    public function testOtherLicencesTable()
    {
        $element = ['details', 'otherLicences', 'table'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementTable($element);

        $element = ['details', 'otherLicences', 'action'];
        $this->assertFormElementHidden($element);

        $element = ['details', 'otherLicences', 'id'];
        $this->assertFormElementHidden($element);

        $element = ['details', 'otherLicences', 'rows'];
        $this->assertFormElementHidden($element);
    }

    public function testFileUpload()
    {
        $element = ['details', 'file'];
        $this->assertFormElementMultipleFileUpload($element);
    }

    public function testId()
    {
        $this->assertFormElementHidden(['details', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['details', 'version']);
    }

    public function testTmType()
    {
        $this->assertFormElementDynamicRadio(['details', 'tmType'], false);
    }

    public function testTmApplicationStatus()
    {
        $this->assertFormElementDynamicSelect(
            ['details', 'tmApplicationStatus'],
            true
        );
    }

    public function testIsOwner()
    {
        $this->assertFormElementRequired(['details', 'isOwner'], true);
    }

    public function testAdditionalInformation()
    {
        $element = ['details', 'additionalInformation'];
        $this->assertFormElementText($element, 0, 4000);
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
