<?php

/**
 * Test Batch Inbox Documents Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\BatchInboxDocumentsProcessingService;

/**
 * Test Batch Inbox Documents Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BatchInboxDocumentsProcessingServiceTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new BatchInboxDocumentsProcessingService();
        $this->sut->setServiceLocator($this->sm);

        $logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($logWriter);
        $this->sm->setService('Zend\Log', $logger);

        $this->sm->setService(
            'Helper\Date',
            m::mock()
            ->shouldReceive('getDateObject')
            ->andReturnUsing(
                function () {
                    // we need a closure here otherwise only one DateTime
                    // instance ever gets created, which then gets mutated
                    // by each call inside the service
                    return new \DateTime('2015-05-14');
                }
            )
            ->getMock()
        );
    }

    public function testProcessWithNoRecords()
    {
        $this->sm->setService(
            'Entity\CorrespondenceInbox',
            m::mock()
            ->shouldReceive('getAllRequiringPrint')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-07T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('getAllRequiringReminder')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-12T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('multiUpdate')
            ->never()
            ->getMock()
        );

        $this->sm->setService(
            'Email',
            m::mock()
            ->shouldReceive('sendTemplate')
            ->never()
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->never()
            ->getMock()
        );

        $this->sut->process();
    }

    public function testProcessWithOneReminderButNoEmailAddresses()
    {
        $this->sm->setService(
            'Entity\CorrespondenceInbox',
            m::mock()
            ->shouldReceive('getAllRequiringPrint')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-07T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('getAllRequiringReminder')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-12T00:00:00+01:00')
            ->andReturn(
                [
                    [
                        'licence' => [
                            'id' => 7,
                            'organisation' => [
                                'id' => 123
                            ]
                        ]
                    ]
                ]
            )
            ->shouldReceive('multiUpdate')
            ->never()
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('getAdminEmailAddresses')
            ->with(123)
            ->andReturn([])
            ->getMock()
        );

        $this->sm->setService(
            'Email',
            m::mock()
            ->shouldReceive('sendTemplate')
            ->never()
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->never()
            ->getMock()
        );

        $this->sut->process();
    }

    public function testProcessWithOneContinuationDetailReminderAndEmailAddresses()
    {
        $this->sm->setService(
            'Entity\CorrespondenceInbox',
            m::mock()
            ->shouldReceive('getAllRequiringPrint')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-07T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('getAllRequiringReminder')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-12T00:00:00+01:00')
            ->andReturn(
                [
                    [
                        'id' => 44,
                        'licence' => [
                            'id' => 7,
                            'licNo' => 'OB123456',
                            'organisation' => [
                                'id' => 123
                            ]
                        ],
                        'document' => [
                            'continuationDetails' => [1, 2]
                        ]
                    ]
                ]
            )
            ->shouldReceive('multiUpdate')
            ->with(
                [
                    [
                        'id' => 44,
                        'emailReminderSent' => 'Y',
                        '_OPTIONS_' => ['force' => true]
                    ]
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('getAdminEmailAddresses')
            ->with(123)
            ->andReturn(['Test User <test@user.com>'])
            ->getMock()
        );

        $this->sm->setService(
            'Helper\Url',
            m::mock()
            ->shouldReceive('fromRouteWithHost')
            ->with('selfserve', 'correspondence_inbox')
            ->andReturn('/inbox')
            ->getMock()
        );

        $this->sm->setService(
            'Email',
            m::mock()
            ->shouldReceive('sendTemplate')
            ->with(
                false,
                null,
                null,
                ['Test User <test@user.com>'],
                'email.inbox-reminder.continuation.subject',
                'markup-email-inbox-reminder-continuation',
                ['OB123456', '/inbox']
            )
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->never()
            ->getMock()
        );

        $this->sut->process();
    }

    public function testProcessWithOneReminderAndEmailAddresses()
    {
        $this->sm->setService(
            'Entity\CorrespondenceInbox',
            m::mock()
            ->shouldReceive('getAllRequiringPrint')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-07T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('getAllRequiringReminder')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-12T00:00:00+01:00')
            ->andReturn(
                [
                    [
                        'id' => 44,
                        'licence' => [
                            'id' => 7,
                            'licNo' => 'OB123456',
                            'organisation' => [
                                'id' => 123
                            ]
                        ],
                        'document' => [
                            'continuationDetails' => []
                        ]
                    ]
                ]
            )
            ->shouldReceive('multiUpdate')
            ->with(
                [
                    [
                        'id' => 44,
                        'emailReminderSent' => 'Y',
                        '_OPTIONS_' => ['force' => true]
                    ]
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('getAdminEmailAddresses')
            ->with(123)
            ->andReturn(['Test User <test@user.com>'])
            ->getMock()
        );

        $this->sm->setService(
            'Helper\Url',
            m::mock()
            ->shouldReceive('fromRouteWithHost')
            ->with('selfserve', 'correspondence_inbox')
            ->andReturn('/inbox')
            ->getMock()
        );

        $this->sm->setService(
            'Email',
            m::mock()
            ->shouldReceive('sendTemplate')
            ->with(
                false,
                null,
                null,
                ['Test User <test@user.com>'],
                'email.inbox-reminder.standard.subject',
                'markup-email-inbox-reminder-standard',
                ['OB123456', '/inbox']
            )
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->never()
            ->getMock()
        );

        $this->sut->process();
    }

    public function testProcessWithTwoPrintRecords()
    {
        $this->sm->setService(
            'Entity\CorrespondenceInbox',
            m::mock()
            ->shouldReceive('getAllRequiringPrint')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-07T00:00:00+01:00')
            ->andReturn(
                [
                    [
                        'id' => 10,
                        'licence' => [
                            'id' => 5
                        ],
                        'document' => [
                            'id' => 50,
                            'foo' => 'bar',
                            'description' => 'A Document'
                        ]
                    ],
                    [
                        'id' => 20,
                        'licence' => [
                            'id' => 2
                        ],
                        'document' => [
                            'id' => 70,
                            'description' => 'Another Document'
                        ]
                    ]
                ]
            )
            ->shouldReceive('getAllRequiringReminder')
            ->with('2015-04-14T00:00:00+01:00', '2015-05-12T00:00:00+01:00')
            ->andReturn([])
            ->shouldReceive('multiUpdate')
            ->with(
                [
                    [
                        'id' => 10,
                        'printed' => 'Y',
                        '_OPTIONS_' => ['force' => true]
                    ],
                    [
                        'id' => 20,
                        'printed' => 'Y',
                        '_OPTIONS_' => ['force' => true]
                    ]
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Email',
            m::mock()
            ->shouldReceive('sendTemplate')
            ->never()
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->with(m::type('\Common\Service\File\File'), 'A Document')
            ->shouldReceive('enqueueFile')
            ->with(m::type('\Common\Service\File\File'), 'Another Document')
            ->getMock()
        );

        $this->sut->process();
    }
}
