<?php

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Mockery as m;
use OlcsTest\Bootstrap;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

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
        $this->markTestSkipped();
        $this->mockController('\Olcs\Controller\Lva\Application\PaymentSubmissionController');
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
                'storedCardReference' => null,
            ],
            [
                'id' => [
                    'transaction' => $paymentId,
                ],
            ]
        );

        $this->expectQuery(
            PaymentByIdQry::class,
            [
                'id' => $paymentId,
            ],
            [
                'reference' => 'the_guid',
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
                'storedCardReference' => null,
            ],
            [
                'messages' => 'there was an error',
            ],
            false
        );

        $this->sut->shouldReceive('addErrorMessage')->once()->with('feeNotPaidError');
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
                'storedCardReference' => null,
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
                ->andReturn('redirectToSummary')
                ->getMock()
            );

        $redirect = $this->sut->indexAction();

        $this->assertEquals('redirectToSummary', $redirect);
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

    /**
     * @group paymentSubmissionController
     */
    public function testPaymentResultActionSuccess()
    {
        $applicationId = 123;
        $paymentId = 99;

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($applicationId);

        $this->expectCommand(
            CompletePaymentCmd::class,
            [
                'reference' => 'OLCS-01-20150113-112403-A6F73058',
                'cpmsData' => $query,
                'paymentMethod' => 'fpm_card_online',
                'submitApplicationId' => $applicationId,
            ],
            [
                'id' => [
                    'transaction' => $paymentId,
                ],
            ]
        );

        $this->expectQuery(
            PaymentByIdQry::class,
            [
                'id' => $paymentId,
            ],
            [
                'status' => [
                    'id' => RefData::TRANSACTION_STATUS_COMPLETE,
                ],
            ]
        );

        $this->sut->shouldReceive('redirect->toRoute')
            ->with(
                'lva-application/summary',
                [
                    'application' => $applicationId,
                    'reference' => 'OLCS-01-20150113-112403-A6F73058',
                ]
            )
            ->andReturn('redirectToSummary');

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToSummary', $redirect);
    }

    /**
     * @dataProvider failureStatusProvider
     */
    public function testPaymentResultActionFailure($paymentStatus, $expectedErrorMsg)
    {
        $applicationId = 123;
        $paymentId = 99;

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($applicationId);

        $this->expectCommand(
            CompletePaymentCmd::class,
            [
                'reference' => 'OLCS-01-20150113-112403-A6F73058',
                'cpmsData' => $query,
                'paymentMethod' => 'fpm_card_online',
                'submitApplicationId' => $applicationId,
            ],
            [
                'id' => [
                    'transaction' => $paymentId,
                ],
            ]
        );

        $this->expectQuery(
            PaymentByIdQry::class,
            [
                'id' => $paymentId,
            ],
            [
                'status' => [
                    'id' => $paymentStatus,
                ],
            ]
        );

        if ($expectedErrorMsg) {
            $this->sut->shouldReceive('addErrorMessage')
                ->once()
                ->with($expectedErrorMsg);
        }

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-application', ['application' => $applicationId])
            ->andReturn('redirectToOverview');

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }

    public function failureStatusProvider()
    {
        return [
            [RefData::TRANSACTION_STATUS_CANCELLED, null],
            [RefData::TRANSACTION_STATUS_FAILED, 'feeNotPaidError'],
            ['unknown_status', 'feeNotPaidError'],
        ];
    }

    public function testPaymentResultActionError()
    {
        $applicationId = 123;

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150113-112403-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($applicationId);

        $this->expectCommand(
            CompletePaymentCmd::class,
            [
                'reference' => 'OLCS-01-20150113-112403-A6F73058',
                'cpmsData' => $query,
                'paymentMethod' => 'fpm_card_online',
                'submitApplicationId' => $applicationId,
            ],
            [
                'messages' => 'there was an error',
            ],
            false
        );

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->with('payment-failed')
            ->shouldReceive('redirect->toRoute')
            ->with('lva-application', ['application' => $applicationId])
            ->andReturn('redirectToOverview');

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToOverview', $redirect);
    }
}
