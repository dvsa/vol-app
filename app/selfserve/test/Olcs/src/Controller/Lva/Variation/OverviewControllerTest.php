<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Entity\VariationCompletionEntityService as Completion;

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

        $this->sut = m::mock('\Olcs\Controller\Lva\Variation\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')
            ->makePartial()
            ->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group variation-overview-controller
     *
     * @dataProvider indexProvider
     *
     * @param array $sectionCompletions
     * @param bool $isReady
     */
    public function testIndexAction($sectionCompletions, $isReady)
    {

        $applicationId  = 3;
        $userId         = 99;
        $organisationId = 101;

        $fee =[
            'id' => 76,
            'amount' => '1234.56',
        ];

        $applicationData = [
            'id' => $applicationId,
            'isVariation' => true,
            'createdOn' => '2015-01-09T10:47:30+0000',
            'status' => [
                'id' => 'apsts_not_submitted',
                'description' => 'Not submitted'
            ],
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

        $this->sut->shouldReceive('getAccessibleSections')->andReturn(
            [
                'addresses',
                'business_details'
            ]
        );

        $this->sm->setService(
            'Processing\VariationSection',
            m::mock()
                ->shouldReceive('setApplicationId')
                    ->with($applicationId)
                    ->andReturnSelf()
                ->shouldReceive('getSectionCompletion')
                    ->andReturn($sectionCompletions)
                ->getMock()
        );

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
                ->with('lva-variation/payment', ['application' => $applicationId])
                ->andReturn('actionUrl')
                ->getMock()
        );

        $mockForm = m::mock()
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
                    ->with($mockForm, 'actionUrl', $fee, true, $isReady)
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Variation\VariationOverview', $response);
    }

    public function indexProvider()
    {
        return [
            [
                [
                    'addresses' => Completion::STATUS_UPDATED,
                    'business_details' => Completion::STATUS_REQUIRES_ATTENTION,
                ],
                false,
            ],
            [
                [
                    'addresses' => Completion::STATUS_UPDATED,
                    'business_details' => Completion::STATUS_UPDATED,
                ],
                true,
            ],
            [
                [
                    'addresses' => Completion::STATUS_UNCHANGED,
                    'business_details' => Completion::STATUS_UNCHANGED,
                ],
                false,
            ],
        ];
    }
}
