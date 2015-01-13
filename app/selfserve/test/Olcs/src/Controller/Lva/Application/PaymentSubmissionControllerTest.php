<?php

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Data\CategoryDataService;

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Application\PaymentSubmissionController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Helper function - common setup for testIndexPost methods
     *
     * @param int $applicationId
     * @param int $licenceId
     * @param int $organisationId
     * @param int $feeId
     * @return null
     */
    protected function indexActionPostSetup($applicationId, $licenceId, $organisationId, $feeId)
    {
        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('getPost')
                ->andReturn(['version' => 1])
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('getLicenceId')
            ->andReturn($licenceId)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with('lva-application/summary', ['application' => $applicationId])
                ->getMock()
            )
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application');

        $this->sut->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result', 'fee' => $feeId],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $fee = $this->getStubFee($feeId);

        $mockFeeService = m::mock()
            ->shouldReceive('getLatestOutstandingFeeForApplication')
            ->with($applicationId)
            ->andReturn($fee)
            ->getMock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

    }

    /**
     * Helper function
     *
     * @param int $feeId
     * @return array
     */
    protected function getStubFee($feeId)
    {
        return [
            'id' => $feeId,
            'amount' => '1234.56',
        ];
    }

    /**
     * Test index action happy path
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostSuccess()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;
        $feeId          = 99;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId, $feeId);

        $update = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'receivedDate' => '2014-12-16 10:10:10',
            'targetCompletionDate' => '2015-02-17 10:10:10'
        );

        $mockApplicationService = m::mock()
            ->shouldReceive('getOrganisation')
                ->with($applicationId)
                ->andReturn(['id' => $organisationId])
            ->shouldReceive('forceUpdate')
                ->with($applicationId, $update)
            ->getMock();

        $task = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'GV79 Application',
            'actionDate' => '2014-01-01',
            'assignedByUser' => 1,
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'isClosed' => 0,
            'application' => $applicationId,
            'licence' => 1
        );

        $mockTaskService = m::mock()
            ->shouldReceive('save')
            ->with($task)
            ->getMock();

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2014-01-01')
            ->shouldReceive('getDateObject')
            ->andReturn(new \DateTime('2014-12-16 10:10:10'))
            ->getMock();

        $fee = $this->getStubFee($feeId);

        $mockCpmsService = m::mock()
            ->shouldReceive('initiateCardRequest')
                ->with(
                    $organisationId, // customerReference
                    $feeId, // salesReference
                    'resultHandlerUrl',
                    array($fee)
                )
                ->andReturn(
                    ['redirection_data' => 'the_guid', 'gateway_url' => 'the_gateway']
                )
            ->getMock();

        $this->sm->setService('Entity\Application', $mockApplicationService);
        $this->sm->setService('Entity\Task', $mockTaskService);
        $this->sm->setService('Helper\Date', $mockDateHelper);
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);

        $view = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);

        $viewData = $view->getVariables();
        $this->assertEquals('the_gateway', $viewData['gateway']);
        $this->assertEquals('the_guid', $viewData['data']['redirectionData']);
    }

    /**
     * Test index action with error response from payment service
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostPaymentError()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;
        $feeId          = 99;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId, $feeId);

        $mockApplicationService = m::mock()
            ->shouldReceive('getOrganisation')
                ->with($applicationId)
                ->andReturn(['id' => $organisationId])
            ->getMock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $mockCpmsService = m::mock()
            ->shouldReceive('initiateCardRequest')
            ->andThrow(new \Common\Service\Cpms\PaymentInvalidResponseException())
            ->getMock();

        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);

        $this->sut->shouldReceive('addErrorMessage')->once();
        $this->sut->shouldReceive('redirectToOverview')->once();

        $this->sut->indexAction();
    }

    /**
     * Test index action (not a HTTP POST)
     *
     * @group paymentSubmissionController
     * @expectedException Common\Exception\BadRequestException
     */
    public function testIndexActionGet()
    {
        $this->sut
            ->shouldReceive('getRequest')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('isPost')
                    ->andReturn(false)
                    ->getMock()
                )
            ->shouldReceive('getApplicationId')
                ->andReturn(123);

        $this->sut->indexAction();
    }

    /**
     * Test index action without application ID
     *
     * @group paymentSubmissionController
     * @expectedException Common\Exception\BadRequestException
     */
    public function testIndexActionPostWithNoApplicationId()
    {
        $this->sut
            ->shouldReceive('getRequest')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('isPost')
                    ->andReturn(true)
                    ->getMock()
                )
            ->shouldReceive('getApplicationId')
                ->andReturn('');

        $this->sut->indexAction();
    }

    public function testSummaryAction()
    {
        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            );
        $this->sut->summaryAction();
    }

    public function testSummaryActionPostGotoDashboard()
    {
        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                    ->andReturn(true)
                ->shouldReceive('getPost')
                    ->andReturn(['submitDashboard' => ''])
                ->getMock()
            );

        $this->sut->shouldReceive('redirect->toRoute')->with('dashboard')
            ->andReturn('redirectToDash');

        $redirect = $this->sut->summaryAction();

        $this->assertEquals('redirectToDash', $redirect);
    }

    public function testSummaryActionPostGotoOverview()
    {
        $applicationId = 123;

        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                    ->andReturn(true)
                ->shouldReceive('getPost')
                    ->andReturn(['submitOverview' => ''])
                ->getMock()
            )
            ->shouldReceive('getApplicationId')
                ->andReturn($applicationId)
            ->shouldReceive('redirect->toRoute')
                ->with('lva-application', ['application' => $applicationId])
                ->andReturn('redirectToOverview');

        $redirect = $this->sut->summaryAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }
}
