<?php

namespace OlcsTest\View\Model\Email;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Model\Email\InspectionRequest as Sut;

/**
 * Class InspectionRequestTest
 * @package OlcsTest\View\Helper
 */
class InspectionRequestTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->sut = new Sut();
    }

    public function testPopulateAllData()
    {
        $this->markTestIncomplete("@TODO");
    }

    public function testPopulateWithMissingData()
    {
        // stub data
        $inspectionRequest = [];
        $user              = [];
        $peopleData        = [];
        $workshops         = [];

        // mocks
        $translator = m::mock();

        // assertions
        $this->assertSame(
            $this->sut,
            $this->sut->populate($inspectionRequest, $user, $peopleData, $workshops, $translator)
        );

        $expected = [
            'inspectionRequestId' => '',
            'currentUserName' => '',
            'currentUserEmail' => '',
            'inspectionRequestDateRequested' => '',
            'inspectionRequestNotes' => '',
            'inspectionRequestDueDate' => '',
            'ocAddress' => null,
            'inspectionRequestType' => '',
            'licenceNumber' => '',
            'licenceType' => '',
            'totAuthVehicles' => '',
            'totAuthTrailers' => '',
            'numberOfOperatingCentres' => '',
            'expiryDate' => '',
            'operatorId' => '',
            'operatorName' => '',
            'operatorEmail' => '',
            'operatorAddress' => null,
            'contactPhoneNumbers' => null,
            'tradingNames' => [],
            'workshopIsExternal' => false,
            'safetyInspectionVehicles' => '',
            'safetyInspectionTrailers' => '',
            'inspectionProvider' => [],
            'people' => [],
            'otherLicences' => [],
            'applicationOperatingCentres' => [],
        ];

        $vars = (array) $this->sut->getVariables();

        $this->assertEquals($expected, $vars);
    }
}
