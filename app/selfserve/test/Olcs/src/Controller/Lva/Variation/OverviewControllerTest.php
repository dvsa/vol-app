<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Common\Service\Entity\VariationCompletionEntityService as Completion;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use OlcsTest\Bootstrap;

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->markTestSkipped();
        
        parent::setUp();

        $this->sut = m::mock('\Olcs\Controller\Lva\Variation\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = $this->getServiceManager();

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
    public function testIndexAction($sections, $sectionCompletions, $isReady)
    {

        $applicationId  = 3;
        $userId         = 99;
        $organisationId = 101;

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
            'licence' => [
                'organisation' => [
                    'id' => $organisationId,
                ],
            ],
            'sections' => $sections,
            'variationCompletion' => $sectionCompletions,
            'outstandingFeeTotal' => '99.99',
            'disableCardPayments' => false,
        ];

        $userData = [
            'id' => $userId,
            'organisationUsers' => [
                [
                    'organisation' => [
                        'id' => $organisationId,
                    ]
                ]
            ]
        ];

        $this->sut->shouldReceive('params')
            ->with('application')
            ->andReturn($applicationId);

        $this->expectQuery(ApplicationQry::class, ['id' => $applicationId], $applicationData, true, 2);
        $this->expectQuery(MyAccount::class, [], $userData);

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
                    ->with($mockForm, 'actionUrl', true, $isReady, '99.99', false)
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
                    'addresses' => [],
                    'business_details' => [],
                ],
                [
                    'addresses' => Completion::STATUS_UPDATED,
                    'business_details' => Completion::STATUS_REQUIRES_ATTENTION,
                ],
                false,
            ],
            [
                [
                    'addresses' => [],
                    'business_details' => [],
                ],
                [
                    'addresses' => Completion::STATUS_UPDATED,
                    'business_details' => Completion::STATUS_UPDATED,
                ],
                true,
            ],
            [
                [
                    'addresses' => [],
                    'business_details' => [],
                ],
                [
                    'addresses' => Completion::STATUS_UNCHANGED,
                    'business_details' => Completion::STATUS_UNCHANGED,
                ],
                false,
            ],
        ];
    }
}
