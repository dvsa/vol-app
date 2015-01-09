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
use Olcs\Controller\Lva\Application\OverviewController as Sut;

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
        $this->sut = m::mock('\Olcs\Controller\Lva\Application\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    protected function indexActionSetUp($fee)
    {
        $applicationId  = 3;
        $userId         = 99;
        $organisationId = 101;

        $applicationData = [
            'id' => $applicationId,
            'applicationCompletions' => [[]],
            'createdOn' => '2015-01-09T10:47:30+0000',
            'status' => ['id' => 'apsts_not_submitted', 'description' => 'Not Submitted'],
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

        // stub accessible sections call
        $this->sut->shouldReceive('getAccessibleSections')
            ->andReturn([]);

        $this->sm->setService(
            'Entity\Fee',
            m::mock()
                ->shouldReceive('getLatestOutstandingFeeForApplication')
                    ->with($applicationId)
                    ->andReturn($fee)
                ->getMock()
        );

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
    public function testIndexActionWithFee()
    {
        $fee =[
            'id' => 76,
            'amount' => '1234.56',
        ];

        $this->indexActionSetUp($fee);

        // controller should set the fee amount on the form
        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('get')
                ->with('amount')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('setTokens')
                        ->with([0 => '1,234.56'])
                    ->getMock()
                )
            ->getMock();
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
                ->getMock()
        );

        $mockForm->shouldReceive('setAttribute')->with('action', 'actionUrl');

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

    /**
     * @group application-overview-controller
     */
    public function testIndexActionWithNoFee()
    {
        $fees = null;

        $this->indexActionSetUp($fees);

        // controller should remove fee amount and update button label
        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('get')
                ->with('submitPay')
                ->once()
                ->andReturn(
                    m::mock()
                        ->shouldReceive('setLabel')
                        ->with('submit-application.button')
                    ->getMock()
                )
            ->getMock();
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
                ->shouldReceive('remove')
                    ->once()
                    ->with($mockForm, 'amount')
                ->getMock()
        );

        $mockForm->shouldReceive('setAttribute')->with('action', 'actionUrl');

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }
}
