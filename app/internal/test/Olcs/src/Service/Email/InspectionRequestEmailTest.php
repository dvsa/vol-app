<?php

/**
 * Email service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Service\Email;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Service\Email\InspectionRequestEmailService as Sut;
use OlcsTest\Bootstrap;

/**
 * Email service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestEmailTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        parent::setUp();

        $this->sut = new Sut();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test send inspection request email method
     */
    public function testSendInspectionRequestEmail()
    {
        $inspectionRequestId = 99;
        $licenceId = 77;
        $organisationId = 55;

        // stub data
        $inspectionRequestData = [
            'foo' => 'bar',
            'licence' => [
                'id' => $licenceId,
                'organisation' => [
                    'id' => $organisationId,
                ],
            ],
        ];

        $userData              = ['USER'];
        $personData            = ['PEOPLE'];
        $workshopData          = ['WORKSHOPS'];

        // mocks
        $mockInspectionRequestService = m::mock();
        $this->sm->setService('Entity\InspectionRequest', $mockInspectionRequestService);
        $mockUserService = m::mock();
        $this->sm->setService('Entity\User', $mockUserService);
        $mockPersonService = m::mock();
        $this->sm->setService('Entity\Person', $mockPersonService);
        $mockWorkshopService = m::mock();
        $this->sm->setService('Entity\Workshop', $mockWorkshopService);
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);
        $mockRenderer = m::mock();
        $this->sm->setService('ViewRenderer', $mockRenderer);

        // expectations
        $mockInspectionRequestService
            ->shouldReceive('getInspectionRequest')
            ->with($inspectionRequestId)
            ->once()
            ->andReturn($inspectionRequestData);

        $mockUserService
            ->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($userData);

        $mockPersonService
            ->shouldReceive('getAllForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($personData);

        $mockWorkshopService
            ->shouldReceive('getForLicence')
            ->with($licenceId)
            ->once()
            ->andReturn($workshopData);

        $this->sut->sendInspectionRequestEmail($inspectionRequestId);
    }
}
