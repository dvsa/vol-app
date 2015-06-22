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
use Dvsa\Olcs\Transfer\Command\Payment\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Query\Payment\Payment as PaymentByIdQry;

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
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with('lva-application/summary', ['application' => $applicationId])
                ->getMock()
            )
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application');
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
        $paymentId = 99;

        $this->setPost(['version' => 1]);

        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->expectCommand(
            PayOutstandingFeesCmd::class,
            [
                'cpmsRedirectUrl' => 'resultHandlerUrl',
                'applicationId'   => $applicationId,
                'paymentMethod'   => 'fpm_card_online',
                'feeIds'          => null,
                'organisationId'  => null,
                'received'        => null,
                'receiptDate'     => null,
                'payer'           => null,
                'slipNo'          => null,
                'chequeNo'        => null,
                'chequeDate'      => null,
                'poNo'            => null,
            ],
            [
                'id' => [
                    'payment' => $paymentId,
                ],
            ]
        );

        $this->expectQuery(
            PaymentByIdQry::class,
            [
                'id' => $paymentId,
            ],
            [
                'guid' => 'the_guid',
                'gatewayUrl' => 'the_gateway',
            ]
        );

        $this->sut
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with('lva-application/summary', ['application' => $applicationId])
                ->getMock()
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
        $paymentId = 99;

        $this->setPost(['version' => 1]);

        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->expectCommand(
            PayOutstandingFeesCmd::class,
            [
                'cpmsRedirectUrl' => 'resultHandlerUrl',
                'applicationId'   => $applicationId,
                'paymentMethod'   => 'fpm_card_online',
                'feeIds'          => null,
                'organisationId'  => null,
                'received'        => null,
                'receiptDate'     => null,
                'payer'           => null,
                'slipNo'          => null,
                'chequeNo'        => null,
                'chequeDate'      => null,
                'poNo'            => null,
            ],
            [
                'messages' => 'there was an error',
            ],
            false
        );

        $this->mockService('Helper\Translation', 'translate')
            ->once()
            ->with('feeNotPaidError')
            ->andReturn('FAIL!');

        $this->sut->shouldReceive('addErrorMessage')->once()->with('FAIL!');
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

        $this->setPost(['version' => 1]);

        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn($applicationId)
            ->shouldReceive('url->fromRoute')
            ->with(
                'lva-application/result',
                ['action' => 'payment-result'],
                ['force_canonical' => true],
                true
            )
            ->andReturn('resultHandlerUrl');

        $this->expectCommand(
            PayOutstandingFeesCmd::class,
            [
                'cpmsRedirectUrl' => 'resultHandlerUrl',
                'applicationId'   => $applicationId,
                'paymentMethod'   => 'fpm_card_online',
                'feeIds'          => null,
                'organisationId'  => null,
                'received'        => null,
                'receiptDate'     => null,
                'payer'           => null,
                'slipNo'          => null,
                'chequeNo'        => null,
                'chequeDate'      => null,
                'poNo'            => null,
            ],
            [
                'id' => [],
                'messages' => [
                    'No fees to pay',
                ],
            ]
        );

        $this->expectCommand(
            SubmitApplicationCmd::class,
            [
                'id' => $applicationId,
                'version' => 1,
            ],
            [
                'id' => [
                    'application' => $applicationId,
                    'licence' => 7,
                    'task' => 11,
                ],
                'messages' => [
                    'Application updated',
                    'Licence updated',
                    'Task created successfully',
                ],
            ]
        );

        $this->sut
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with(
                    'lva-application/summary',
                    ['application' => $applicationId, 'reference' => null]
                )
                ->once()
                ->getMock()
            );

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
            ->with(
                'lva-application/summary',
                [
                    'application' => $applicationId,
                    'reference' => 'OLCS-01-20150113-112403-A6F73058',
                ]
            )
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


    // @TODO these test helper methods could be reused elsewhere

    /**
     * @param string $class command class that should be called
     * @param array $expectedDtoData
     * @param array $result
     */
    protected function expectCommand($class, array $expectedDtoData, array $result, $ok = true)
    {
        return $this->mockCommandOrQueryCall('handleCommand', $class, $expectedDtoData, $result, $ok);
    }

    /**
     * @param string $class query class that should be called
     * @param array $expectedDtoData
     * @param array $result
     */
    protected function expectQuery($class, array $expectedDtoData, array $result, $ok = true)
    {
        return $this->mockCommandOrQueryCall('handleQuery', $class, $expectedDtoData, $result, $ok);
    }

    /**
     * @param string $method controller/plugin method to mock
     * @param string $class query class that should be called
     * @param array $expectedDtoData
     * @param array $result
     */
    private function mockCommandOrQueryCall($method, $class, array $expectedDtoData, array $result, $ok = true)
    {
        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn($ok)
            ->shouldReceive('getResult')
            ->andReturn($result)
            ->getMock();

        $this->sut
            ->shouldReceive($method)
            ->once()
            ->with(
                m::on(
                    function ($cmd) use ($expectedDtoData, $class) {
                        $matched = (
                            is_a($cmd, $class)
                            &&
                            $cmd->getArrayCopy() == $expectedDtoData
                        );
                        return $matched;
                    }
                )
            )
            ->andReturn($response);
    }
}
