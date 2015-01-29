<?php

/**
 * Payment Submission Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variataion;

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

        parse_str(
            'state=0.98269600+1421148242&receipt_reference=OLCS-01-20150129-141612-A6F73058&code=801\
            &message=Successful+payment+received)',
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
            ->with($query, array($fee))
            ->andReturn(PaymentEntityService::STATUS_PAID);

        $update = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'receivedDate' => '2015-01-29 10:10:10',
            'targetCompletionDate' => '2015-04-02 10:10:10'
        );
        $this->mockEntity('Application', 'forceUpdate')
            ->with($applicationId, $update)
            ->once();

        $this->mockService('Processing\Task', 'getAssignment')
            ->with(['category' => CategoryDataService::CATEGORY_APPLICATION])
            ->andReturn(
                [
                    'assignedToUser' => 456,
                    'assignedToTeam' => 789
                ]
            );
        $task = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => $expectedTaskDescription,
            'actionDate' => '2015-01-29',
            'assignedByUser' => 1,
            'assignedToUser' => 456,
            'assignedToTeam' => 789,
            'isClosed' => 0,
            'application' => $applicationId,
            'licence' => 234
        );

        $this->mockEntity('Task', 'save')
            ->with($task);

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
