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

    protected function indexActionSetUp($fees)
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
                ->shouldReceive('getOutstandingFeesForApplication')
                    ->with($applicationId)
                    ->andReturn($fees)
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
        $fees = [
            [
                'id' => 76,
                'amount' => '1234.56',
            ]
        ];

        $this->indexActionSetUp($fees);

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
        $fees = [];

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

    /**
     * Test the helper function that gets the latest fee from an array
     * of outstanding fees.
     *
     * If two fees have the same invoice date, we should get the one with
     * the higher id (primary key)
     *
     * @group application-overview-controller
     */
    public function testGetLatestFee()
    {
        $fees = [
            [
                'amount' => '251.75',
                'invoicedDate' => '2013-11-22T00:00:00+0000',
                'id' => 77,
            ],
            [
                'amount' => '254.40',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 78,
            ],
            [
                'amount' => '250.50',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 76,
            ],
            [
                'amount' => '150.00',
                'invoicedDate' => '2013-11-21T00:00:00+0000',
                'id' => 79,
            ],
        ];

        $sut = new Sut();

        $this->assertEquals(
            [
                'amount' => '254.40',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 78,
            ],
            $sut->getLatestFee($fees)
        );
    }
}
