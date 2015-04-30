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
            'id' => $inspectionRequestId,
            'foo' => 'bar',
            'licence' => [
                'id' => $licenceId,
                'organisation' => [
                    'id' => $organisationId,
                ],
                'enforcementArea' => [
                    'emailAddress' => 'ea@example.com',
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
        $mockEmailService = m::mock();
        $this->sm->setService('email', $mockEmailService);
        $mockView = m::mock();
        $mockConfig = [
            'email' => [
                'inspection_request' => [
                    'from_name' => 'OLCS TEST',
                    'from_address' => 'olcs@example.com',
                ],
            ],
        ];
        $this->sm->setService('config', $mockConfig);

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

        $mockRenderer
            ->shouldReceive('render')
            ->once()
            ->with($mockView)
            ->andReturn('EMAIL_BODY');

        $expectedSubject = '[ Maintenance Inspection ] REQUEST=99,STATUS=';
        $mockEmailService
            ->shouldReceive('sendEmail')
            ->with('olcs@example.com', 'OLCS TEST', 'ea@example.com', $expectedSubject, 'EMAIL_BODY', false)
            ->once()
            ->andReturn(true);

        $mockView
            ->shouldReceive('populate')
            ->once()
            ->with($inspectionRequestData, $userData, $personData, $workshopData, $mockTranslator)
            ->andReturnSelf();

        $this->assertTrue($this->sut->sendInspectionRequestEmail($mockView, $inspectionRequestId));
    }
}
