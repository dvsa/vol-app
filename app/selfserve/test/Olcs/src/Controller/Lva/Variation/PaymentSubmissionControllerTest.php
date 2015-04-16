<?php

/**
 * Payment Submission Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Entity\PaymentEntityService;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Common\Service\Processing\ApplicationSnapshotProcessingService;

/**
 * Payment Submission Controller Test
 *
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
        $this->mockController('\Olcs\Controller\Lva\Variation\PaymentSubmissionController');
    }

    /**
     * @dataProvider resultSuccessProvider
     * @param int $applicationId
     * @param int $licenceId
     * @param string $goodsOrPsv
     * @param boolean $isUpgrade
     * @param string $expectedTaskDescription
     */
    public function testPaymentResultActionSuccess(
        $applicationId,
        $licenceId,
        $goodsOrPsv,
        $isUpgrade,
        $expectedTaskDescription
    ) {
        $feeId = 99;
        $fee = [
            'id' => $feeId,
            'amount' => '1234.56',
        ];

        $mockSnapshot = m::mock();
        $this->setService('Processing\ApplicationSnapshot', $mockSnapshot);
        $mockSnapshot->shouldReceive('storeSnapshot')
            ->with(123, ApplicationSnapshotProcessingService::ON_SUBMIT);

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150129-141612-A6F73058&code=801
            &message=Successful+payment+received',
            $query
        );

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

        $this->mockEntity('Fee', 'getOverview')
            ->with($feeId)
            ->andReturn($fee);

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->once()
            ->with($query, 'fpm_card_online')
            ->andReturn(PaymentEntityService::STATUS_PAID);

        $this->mockService('Processing\Application', 'submitApplication')
            ->once()
            ->with($applicationId);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-variation/summary', ['application' => $applicationId])
            ->andReturn('redirectToSummary');

        $this->mockService('Helper\Date', 'getDate')
            ->andReturn('2015-01-29');

        $this->mockService('Helper\Date', 'getDateObject')
            ->andReturn(new \DateTime('2015-01-29 10:10:10'));

        $this->mockService('Entity\Application', 'getDataForPaymentSubmission')
            ->with($applicationId)
            ->andReturn(
                [
                    'id' => $applicationId,
                    'goodsOrPsv' => ['id' => $goodsOrPsv]
                ]
            );

        $this->mockService('Processing\VariationSection', 'isLicenceUpgrade')
            ->with($applicationId)
            ->andReturn($isUpgrade);

        $redirect = $this->sut->paymentResultAction();

        $this->assertEquals('redirectToSummary', $redirect);
    }

    public function resultSuccessProvider()
    {
        return [
            [123, 234, 'lcat_gv', true, 'GV80A Application'],
            [123, 234, 'lcat_gv', false, 'GV81 Application'],
            [123, 234, 'lcat_psv', true, 'PSV431A Application'],
            [123, 234, 'lcat_psv', false, 'PSV431 Application'],
        ];
    }
}
