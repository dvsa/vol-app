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
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Entity\PaymentEntityService;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Common\Service\Processing\ApplicationSnapshotProcessingService;

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionControllerTest extends AbstractLvaControllerTestCase
{
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
    protected function indexActionPostSetup($applicationId, $licenceId, $organisationId)
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
            ->andReturn('application');

        $this->mockEntity('Application', 'getOrganisation')
            ->with($applicationId)
            ->andReturn(['id' => $organisationId]);

        $this->mockEntity('Application', 'getCategory')
            ->with($applicationId)
            ->andReturn(LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE);
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
        $interimFeeId   = 100;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $fee = $this->getStubFee($feeId);
        $interimFee = $this->getStubFee($interimFeeId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn($fee);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn($interimFee);

        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($fee)
            ->andReturn(false);
        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($interimFee)
            ->andReturn(false);

        $this->sut->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->with(
                $organisationId, // customerReference
                'resultHandlerUrl',
                array($fee, $interimFee)
            )
            ->andReturn(
                ['receipt_reference' => 'the_guid', 'gateway_url' => 'the_gateway']
            );

        $view = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);

        $viewData = $view->getVariables();
        $this->assertEquals('the_gateway', $viewData['gateway']);
        $this->assertEquals('the_guid', $viewData['data']['receipt_reference']);
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
        $interimFeeId   = 100;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $fee = $this->getStubFee($feeId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn($fee);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn(null);

        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($fee)
            ->andReturn(false);

        $this->sut->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->andThrow(new \Common\Service\Cpms\Exception\PaymentInvalidResponseException())
            ->getMock();

        $this->sut->shouldReceive('addErrorMessage')->once();
        $this->sut->shouldReceive('redirectToOverview')->once();

        $this->sut->indexAction();
    }

    /**
     * Test index action with no fee to pay
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostNoFee()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;

        $mockSnapshot = m::mock();
        $this->setService('Processing\ApplicationSnapshot', $mockSnapshot);
        $mockSnapshot->shouldReceive('storeSnapshot')
            ->with(123, ApplicationSnapshotProcessingService::ON_SUBMIT);

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn(null);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn(null);

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->once();

        $this->mockEntity('Licence', 'forceUpdate')
            ->with($licenceId, ['status' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION])
            ->once();

        $this->mockService('Processing\Application', 'submitApplication')
            ->once()
            ->with($applicationId);

        $this->sut->shouldReceive('redirectToSummary')->once();

        $this->sut->indexAction();
    }


    /**
     * Test index action with a fee that has been paid but 'abandoned', i.e.
     * the card payment succeeded but we did not receive notification
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostFeeOutstandingButPaid()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;
        $feeId          = 99;

        $mockSnapshot = m::mock();
        $this->setService('Processing\ApplicationSnapshot', $mockSnapshot);
        $mockSnapshot->shouldReceive('storeSnapshot')
            ->with(123, ApplicationSnapshotProcessingService::ON_SUBMIT);

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $fee = $this->getStubFee($feeId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn($fee);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn(null);

        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->once()
            ->with($fee)
            ->andReturn(true);
        $this->mockService('Cpms\FeePayment', 'resolveOutstandingPayments')
            ->once()
            ->with($fee)
            ->andReturn(true);

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->once();

        $this->mockEntity('Licence', 'forceUpdate')
            ->with($licenceId, ['status' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION])
            ->once();

        $this->mockService('Processing\Application', 'submitApplication')
            ->once()
            ->with($applicationId);

        $this->sut->shouldReceive('redirectToSummary')->once();

        $this->sut->indexAction();
    }

    /**
     * Test index action with a fee payment that has been initiated but not
     * completed
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostFeeOutstandingNotPaid()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;
        $feeId          = 99;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $fee = $this->getStubFee($feeId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn($fee);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn(null);

        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($fee)
            ->andReturn(true);

        $this->mockService('Cpms\FeePayment', 'resolveOutstandingPayments')
            ->with($fee)
            ->andReturn(false);

        $this->sut->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->with(
                $organisationId, // customerReference
                'resultHandlerUrl',
                array($fee)
            )
            ->andReturn(
                ['receipt_reference' => 'the_guid', 'gateway_url' => 'the_gateway']
            );

        $view = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);

        $viewData = $view->getVariables();
        $this->assertEquals('the_gateway', $viewData['gateway']);
        $this->assertEquals('the_guid', $viewData['data']['receipt_reference']);
    }

    /**
     * Test index action with a fee payment with one fee that has been initiated
     * but not completed, and one fee that has no outstanding payments
     *
     * @group paymentSubmissionController
     */
    public function testIndexActionPostFeeOneOutstandingPaid()
    {
        $applicationId  = 123;
        $licenceId      = 234;
        $organisationId = 456;
        $feeId          = 99;
        $interimFeeId   = 100;

        $this->indexActionPostSetup($applicationId, $licenceId, $organisationId);

        $fee = $this->getStubFee($feeId);
        $interimFee = $this->getStubFee($interimFeeId);

        $this->mockService('Processing\Application', 'getApplicationFee')
            ->with($applicationId)
            ->andReturn($fee);
        $this->mockService('Processing\Application', 'getInterimFee')
            ->with($applicationId)
            ->andReturn($interimFee);

        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($fee)
            ->andReturn(true);
        $this->mockService('Cpms\FeePayment', 'hasOutstandingPayment')
            ->with($interimFee)
            ->andReturn(false);

        $this->mockService('Cpms\FeePayment', 'resolveOutstandingPayments')
            ->with($fee)
            ->andReturn(true);

        $this->sut->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->with(
                $organisationId,
                'resultHandlerUrl',
                array($interimFee)
            )
            ->andReturn(
                ['receipt_reference' => 'the_guid', 'gateway_url' => 'the_gateway']
            );

        $view = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);

        $viewData = $view->getVariables();
        $this->assertEquals('the_gateway', $viewData['gateway']);
        $this->assertEquals('the_guid', $viewData['data']['receipt_reference']);
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

        $this->stubTranslator();

        $fee = $this->getStubFee($feeId);

        $this->mockEntity('Fee', 'getOverview')
            ->with($feeId)
            ->andReturn($fee);

    }

    /**
     * @group paymentSubmissionController
     */
    public function testPaymentResultActionSuccess()
    {
        $applicationId = 123;
        $feeId = 99;
        $licenceId = 321;

        $mockSnapshot = m::mock();
        $this->setService('Processing\ApplicationSnapshot', $mockSnapshot);
        $mockSnapshot->shouldReceive('storeSnapshot')
            ->with(123, ApplicationSnapshotProcessingService::ON_SUBMIT);

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, 'fpm_card_online') // FeePaymentEntityService::METHOD_CARD_ONLINE
            ->andReturn(PaymentEntityService::STATUS_PAID);

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->once();

        $this->mockEntity('Licence', 'forceUpdate')
            ->with($licenceId, ['status' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION])
            ->once();

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-application/summary', ['application' => $applicationId])
            ->andReturn('redirectToSummary');

        $this->mockService('Processing\Application', 'submitApplication')
            ->once()
            ->with($applicationId);

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
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, 'fpm_card_online')
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
            ['Common\Service\Cpms\Exception\PaymentInvalidStatusException'],
            ['Common\Service\Cpms\Exception\PaymentNotFoundException']
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
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->paymentResultActionSetup($query, $applicationId, $feeId);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, 'fpm_card_online')
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

    public function taskDescriptionProvider()
    {
        return array(
            array('lcat_gv', 'ltyp_r', 'GV79 Application'),
            array('lcat_gv', 'ltyp_si', 'GV79 Application'),
            array('lcat_gv', 'ltyp_sn', 'GV79 Application'),
            array('lcat_gv', 'ltyp_sr', 'GV79 Application'),
            array('lcat_psv', 'ltyp_r', 'PSV421 Application'),
            array('lcat_psv', 'ltyp_si', 'PSV421 Application'),
            array('lcat_psv', 'ltyp_sn', 'PSV421 Application'),
            array('lcat_psv', 'ltyp_sr', 'PSV356 Application'),
            array('test', 'test', 'Application')
        );
    }

    /**
     * @dataProvider taskDescriptionProvider
     */
    public function testGetTaskDescription($applicationType, $licenceType, $expected)
    {
        $applicationId = 1;

        $this->mockEntity('Application', 'getDataForValidating')
            ->with($applicationId)
            ->andReturn(
                array(
                    'goodsOrPsv' => $applicationType,
                    'licenceType' => $licenceType
                )
            );

        $this->assertEquals(
            $expected,
            $this->sut->getTaskDescription($applicationId)
        );
    }
}
