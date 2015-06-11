<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends MockeryTestCase
{

    protected $sm;

    protected $sut;

    public function setUp()
    {
        parent::setUp();

        $this->sut = m::mock('\Olcs\Controller\Lva\Application\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sm->setService(
            'Helper\Restriction', // we'll stub this for now
            m::mock()->shouldReceive('isRestrictionSatisfied')->andReturn(true)
        );

        $this->sut->setServiceLocator($this->sm);
    }

    protected function indexActionSetUp($applicationId, $statusId, $statusDescription, $accessibleSections = [])
    {
        $userId         = 99;
        $organisationId = 101;

        $applicationData = [
            'id' => $applicationId,
            'applicationCompletion' => [],
            'createdOn' => '2015-01-09T10:47:30+0000',
            'status' => ['id' => $statusId, 'description' => $statusDescription],
            'createdOn' => '2015-01-09T10:47:30+0000',
            'receivedDate' => null,
            'targetCompletionDate' => null,
        ];

        $this->sut->shouldReceive('params')
            ->with('application')
            ->andReturn($applicationId);

        $this->sm->setService(
            'Entity\Application',
            m::mock()
                ->shouldReceive('getOverview')
                    ->with($applicationId)
                    ->andReturn($applicationData)
                ->shouldReceive('doesBelongToOrganisation')
                    ->with($applicationId, $organisationId)
                    ->andReturn(true)
                ->getMock()
        );
        $this->sm->setService(
            'Entity\User',
            m::mock()
                ->shouldReceive('getCurrentUser')
                    ->withNoArgs()
                    ->andReturn(['id' => $userId])
                ->getMock()
        );
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
                ->shouldReceive('getForUser')
                    ->with($userId)
                    ->andReturn(['id' => $organisationId])
                ->getMock()
        );

        $this->sut->shouldReceive('currentUser->getUserData')->andReturn(['id' => $userId]);

        // stub accessible sections call
        $this->sut->shouldReceive('getAccessibleSections')
            ->andReturn($accessibleSections);

        $this->sut->shouldReceive('url')->andReturn(
            m::mock()
                ->shouldReceive('fromRoute')
                ->with('lva-application/payment', ['application' => $applicationId])
                ->andReturn('actionUrl')
                ->getMock()
        );
    }

    /**
     * @group application-overview-controller
     */
    public function testIndexActionReadyToSubmit()
    {
        $applicationId = 3;

        $this->indexActionSetUp($applicationId, 'apsts_not_submitted', 'Not submitted');

        $mockForm = m::mock('\Zend\Form\Form')
            ->shouldReceive('setData')
                ->once()
            ->getMock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->once()
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
                ->getMock()
        );
        $this->sm->setService(
            'Helper\PaymentSubmissionForm',
            m::mock()
                ->shouldReceive('updatePaymentSubmissonForm')
                    ->once()
                    ->with($mockForm, 'actionUrl', $applicationId, true, true) // button visible and enabled
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

    /**
     * @group application-overview-controller
     */
    public function testIndexActionAlreadySubmitted()
    {
        $applicationId = 3;

        $this->indexActionSetUp($applicationId, 'apsts_consideration', 'Under consideration');

        $mockForm = m::mock('\Zend\Form\Form')
            ->shouldReceive('setData')
            ->once()
            ->getMock();
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->once()
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
              ->getMock()
        );
        $this->sm->setService(
            'Helper\PaymentSubmissionForm',
            m::mock()
                ->shouldReceive('updatePaymentSubmissonForm')
                    ->once()
                    ->with($mockForm, 'actionUrl', $applicationId, false, m::any())  // button not visible
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

     /**
     * @group application-overview-controller
     */
    public function testIndexActionIncomplete()
    {
        $applicationId = 3;

        $this->indexActionSetUp(
            $applicationId,
            'apsts_not_submitted',
            'Not submitted',
            [
                'type_of_licence' => ['enabled' => true, 'complete' => true ],
                'business_type' => ['enabled' => true, 'complete' => false ], // incomplete section
            ]
        );

        $mockForm = m::mock('\Zend\Form\Form')
            ->shouldReceive('setData')
            ->once()
            ->getMock();
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
              ->getMock()
        );
        $this->sm->setService(
            'Helper\PaymentSubmissionForm',
            m::mock()
                ->shouldReceive('updatePaymentSubmissonForm')
                    ->once()
                    ->with($mockForm, 'actionUrl', $applicationId, true, false) // button visible but disabled
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

    /**
     * @group application-overview-controller
     */
    public function testIndexActionNoAccess()
    {
        $applicationId  = 3;

        $this->sut->shouldReceive('params')
            ->with('application')
            ->andReturn($applicationId);

        $this->sut->shouldReceive('checkAccess')
            ->with($applicationId)
            ->andReturn(false);

        $this->sut->shouldReceive('redirect->toRoute')->with('dashboard');

        $this->sut->indexAction();
    }
}
