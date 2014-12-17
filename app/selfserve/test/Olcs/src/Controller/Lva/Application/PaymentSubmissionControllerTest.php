<?php

/**
 * Payment Submission Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
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
     * Test index action
     * 
     * @group paymentSubmissionController
     */
    public function testIndexAction()
    {
        $this->sut
            ->shouldReceive('getApplicationId')
            ->andReturn(1)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('getPost')
                ->andReturn(['version' => 1])
                ->getMock()
            )
            ->shouldReceive('getLicenceId')
            ->andReturn(1)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with('lva-application/summary', ['application' => 1])
                ->getMock()
            )
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application');

        $update = array(
            'id' => 1,
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'version' => 1,
            'receivedDate' => '2014-12-16 10:10:10',
            'targetCompletionDate' => '2015-02-17 10:10:10'
        );

        $mockApplicationService = m::mock()
            ->shouldReceive('save')
            ->with($update)
            ->getMock();

        $task = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'GV79 Application',
            'actionDate' => '2014-01-01',
            'assignedByUser' => 1,
            'assignedToUser' => 1,
            'isClosed' => 0,
            'application' => 1,
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

        $this->sm->setService('Entity\Application', $mockApplicationService);
        $this->sm->setService('Entity\Task', $mockTaskService);
        $this->sm->setService('Helper\Date', $mockDateHelper);

        $this->sut->indexAction();
    }
}
