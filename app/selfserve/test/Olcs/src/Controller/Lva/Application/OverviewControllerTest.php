<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

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

        $this->sut = m::mock('\Olcs\Controller\Lva\Application\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = $this->getServiceManager();

        $this->sm->setService(
            'Helper\Restriction', // we'll stub this for now
            m::mock()
                ->shouldReceive('isRestrictionSatisfied')
                ->andReturn(true)
                ->getMock()
        );

        $this->sut->setServiceLocator($this->sm);
    }

    protected function indexActionSetUp($applicationId, $statusId, $statusDescription, $completion = [], $sections = [])
    {
        $userId         = 99;
        $organisationId = 101;

        $applicationData = [
            'id' => $applicationId,
            'createdOn' => '2015-01-09T10:47:30+0000',
            'status' => ['id' => $statusId, 'description' => $statusDescription],
            'receivedDate' => null,
            'targetCompletionDate' => null,
            'licence' => [
                'organisation' => [
                    'id' => $organisationId,
                ],
            ],
            'applicationCompletion' => $completion,
            'sections' => $sections,
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

        $this->expectQuery(ApplicationQry::class, ['id' => $applicationId], $applicationData);
        $this->expectQuery(MyAccount::class, [], $userData);

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
    public function testIndexActionReadyToSubmit()
    {
        $applicationId = 3;

        $this->indexActionSetUp($applicationId, 'apsts_not_submitted', 'Not submitted');

        $mockForm = m::mock('\Zend\Form\Form')
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
                    ->with($mockForm, 'actionUrl', true, true, '99.99', false) // button visible and enabled
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

    /**
     * @group application-overview-controller
     */
    public function testIndexActionAlreadySubmitted()
    {
        $applicationId = 3;

        $this->indexActionSetUp($applicationId, 'apsts_consideration', 'Under consideration');

        $mockForm = m::mock('\Zend\Form\Form')
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
                    ->with($mockForm, 'actionUrl', false, true, '99.99', false)  // button not visible
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }

     /**
     * @group application-overview-controller
     */
    public function testIndexActionIncomplete()
    {
        $applicationId = 3;

        $this->indexActionSetUp(
            $applicationId,
            'apsts_not_submitted',
            'Not submitted',
            [
                'businessTypeStatus' => null,
                'typeOfLicenceStatus' => 2,
                'undertakingsStatus' => 2,
                'vehiclesDeclarationsStatus' => null,
                'vehiclesPsvStatus' => null,
                'vehiclesStatus' => 2,
            ],
            [
                'type_of_licence' => [],
                'business_type' => [
                    'prerequisite' => [
                        'type_of_licence'
                    ]
                ],
            ]
        );

        $mockForm = m::mock('\Zend\Form\Form')
            ->shouldReceive('setData')
            ->once()
            ->getMock();
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                    ->with('Lva\PaymentSubmission')
                    ->andReturn($mockForm)
              ->getMock()
        );
        $this->sm->setService(
            'Helper\PaymentSubmissionForm',
            m::mock()
                ->shouldReceive('updatePaymentSubmissonForm')
                    ->once()
                    ->with($mockForm, 'actionUrl', true, false, '99.99', false) // button visible but disabled
                ->getMock()
        );

        $response = $this->sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\Application\ApplicationOverview', $response);
    }
}
