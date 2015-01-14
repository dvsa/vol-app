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
use Common\Service\Entity\PaymentEntityService;
use Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait;

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionControllerTest extends MockeryTestCase
{
    use LvaControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->mockController('\Olcs\Controller\Lva\Application\PaymentSubmissionController');
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
        $this->setPost(['version' => 1]);

        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
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
            ->andReturn('application')
            ->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result', 'fee' => $feeId],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $fee = $this->getStubFee($feeId);

        $this->mockEntity('Fee', 'getLatestOutstandingFeeForApplication')
            ->with($applicationId)
            ->andReturn($fee);

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

        $this->mockEntity('Application', 'getOrganisation')
            ->with($applicationId)
            ->andReturn(['id' => $organisationId]);

        $this->mockEntity('Application', 'forceUpdate')
            ->with($applicationId, $update);

        $fee = $this->getStubFee($feeId);

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->with(
                $organisationId, // customerReference
                $feeId, // salesReference
                'resultHandlerUrl',
                array($fee)
            )
            ->andReturn(
                ['redirection_data' => 'the_guid', 'gateway_url' => 'the_gateway']
            );

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

        $this->mockEntity('Application', 'getOrganisation')
            ->with($applicationId)
            ->andReturn(['id' => $organisationId]);

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->andThrow(new \Common\Service\Cpms\PaymentInvalidResponseException())
            ->getMock();

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
        $this->sut->shouldReceive('getApplicationId')->andReturn(123);

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
        $this->setPost([]);

        $this->sut->shouldReceive('getApplicationId')->andReturn('');

        $this->sut->indexAction();
    }

    public function testSummaryAction()
    {
        $this->sut->summaryAction();
    }

    public function testSummaryActionPostGotoDashboard()
    {
        $this->setPost(['submitDashboard' => '']);

        $this->sut->shouldReceive('redirect->toRoute')->with('dashboard')
            ->andReturn('redirectToDash');

        $redirect = $this->sut->summaryAction();

        $this->assertEquals('redirectToDash', $redirect);
    }

    public function testSummaryActionPostGotoOverview()
    {
        $applicationId = 123;

        $this->setPost(['submitOverview' => '']);

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('redirect->toRoute')
            ->with('lva-application', ['application' => $applicationId])
            ->andReturn('redirectToOverview');

        $redirect = $this->sut->summaryAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }


    protected function paymentResultActionSetup(array $query, $applicationId, $feeId)
    {
        $licenceId = 234;

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('params')
            ->with('fee')
            ->andReturn($feeId)
            ->shouldReceive('getLicenceId')
            ->andReturn($licenceId);

        $this->mockTranslator();

        $fee = $this->getStubFee($feeId);

        $this->mockEntity('Fee', 'getOverview')
            ->with($feeId)
            ->andReturn($fee);

    }

    public function testPaymentResultActionSuccess()
    {
        $applicationId = 123;
        $feeId = 99;
        $fee = $this->getStubFee($feeId);

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801\
            &message=Successful+payment+received)',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, array($fee))
            ->andReturn(PaymentEntityService::STATUS_PAID);

        $this->mockEntity('Application', 'forceUpdate')
            ->with($applicationId, m::any())
            ->once();

        $task = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'GV79 Application',
            'actionDate' => '2014-01-01',
            'assignedByUser' => 1,
            'assignedToUser' => 456,
            'assignedToTeam' => 789,
            'isClosed' => 0,
            'application' => $applicationId,
            'licence' => 234
        );

        $this->mockEntity('Task', 'save')
            ->with($task);

        $this->mockService('Processing\Task', 'getAssignment')
            ->with(['category' => CategoryDataService::CATEGORY_APPLICATION])
            ->andReturn(
                [
                    'assignedToUser' => 456,
                    'assignedToTeam' => 789
                ]
            );

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-application/summary', ['application' => $applicationId])
            ->andReturn('redirectToSummary');

        $this->mockService('Helper\Date', 'getDate')
            ->andReturn('2014-01-01');

        $this->mockService('Helper\Date', 'getDateObject')
            ->andReturn(new \DateTime('2014-12-16 10:10:10'));

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToSummary', $redirect);
    }

    /**
     * @dataProvider exceptionFromPaymentServiceProvider
     */
    public function testPaymentResultActionExceptionFromPaymentService($exceptionClass)
    {
        $applicationId = 123;
        $feeId = 99;
        $fee = $this->getStubFee($feeId);

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801\
            &message=Successful+payment+received)',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, array($fee))
            ->andThrow(new $exceptionClass());

        $this->mockEntity('Application', 'forceUpdate')
            ->never();

        $this->mockEntity('Task', 'save')
            ->never();

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->shouldReceive('redirect->toRoute')
            ->with('lva-application', ['application' => $applicationId])
            ->andReturn('redirectToOverview');

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }

    public function exceptionFromPaymentServiceProvider()
    {
        return [
            ['Common\Service\Cpms\PaymentInvalidStatusException'],
            ['Common\Service\Cpms\PaymentNotFoundException']
        ];
    }

    /**
     * @dataProvider failureFromPaymentServiceProvider
     */
    public function testPaymentResultActionFailureFromPaymentService($responseCode)
    {
        $applicationId = 123;
        $feeId = 99;
        $fee = $this->getStubFee($feeId);

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801\
            &message=Successful+payment+received)',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, array($fee))
            ->andReturn($responseCode);

        $this->mockEntity('Application', 'forceUpdate')
            ->never();

        $this->mockEntity('Task', 'save')
            ->never();

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->shouldReceive('redirect->toRoute')
            ->with('lva-application', ['application' => $applicationId])
            ->andReturn('redirectToOverview');

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }

    public function failureFromPaymentServiceProvider()
    {
        return [
            [PaymentEntityService::STATUS_CANCELLED],
            [PaymentEntityService::STATUS_FAILED],
            ['unknown_status']
        ];
    }

    /**
     * Helper method to mock the translator service
     *
     * @return null
     */
    protected function mockTranslator()
    {
        $this->mockService('translator', 'translate')
            ->andReturnUsing(
                function ($input) {
                    return $input;
                }
            );
    }
}
