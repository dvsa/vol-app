<?php

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\BatchInspectionRequestEmailProcessingService;

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BatchInspectionRequestEmailProcessingServiceTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new BatchInspectionRequestEmailProcessingService();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test logging - message should go to logger and console
     */
    public function testLogging()
    {
        // mocks
        $mockConsole = m::mock('\Zend\Console\Adapter\Posix');
        $this->sut->setConsoleAdapter($mockConsole);

        $logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($logWriter);
        $this->sm->setService('Zend\Log', $logger);

        // expectations
        $mockConsole
            ->shouldReceive('writeLine')
            ->once()
            ->with('message');

        $this->sut->log('message');

        // assertions
        $this->assertEquals('message', $logWriter->events[0]['message']);
    }

    /**
     * Test process method
     */
    public function testProcess()
    {
        // stub data
        $emails = [
            1 => '4355',
            2 => '4356',
        ];
        $email1 = [
            'subject' => '[ Maintenance Inspection ] REQUEST=23456,STATUS=S',
        ];
        $email2 = [
            'subject' => '[ Maintenance Inspection ] REQUEST=23456,STATUS=U',
        ];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);

        // expectations
        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', [], false)
            ->once()
            ->andReturn($emails);

        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', ['id' => '4355'], false)
            ->andReturn($email1)
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', ['id' => '4356'], false)
            ->andReturn($email2);

        $this->sut->process();
    }

    /**
     * Test process when email retrieval fails
     */
    public function testProcessEmailFail()
    {
        // stub data
        $emails = [
            1 => '4355',
        ];
        $email1 = ['error'];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);
        $logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($logWriter);
        $this->sm->setService('Zend\Log', $logger);

        // expectations
        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', [], false)
            ->once()
            ->andReturn($emails);

        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', ['id' => '4355'], false)
            ->andReturn($email1);

        $this->sut->process();

        $this->assertRegexp('/Could not retrieve email 4355/', $logWriter->events[0]['message']);
    }

    /**
     * Test process when email subject is invalid
     */
    public function testProcessEmailInvalidSubject()
    {
        // stub data
        $emails = [
            1 => '4355',
        ];
        $email1 = [
            'subject' => 'spam!'
        ];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);
        $logWriter = new \Zend\Log\Writer\Mock();
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($logWriter);
        $this->sm->setService('Zend\Log', $logger);

        // expectations
        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', [], false)
            ->once()
            ->andReturn($emails);

        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', ['id' => '4355'], false)
            ->andReturn($email1)
            ->shouldReceive('makeRestCall')
            ->with('email\inspection-request', 'DELETE', ['id' => '4355'], null);

        $this->sut->process();

        $this->assertRegexp(
            '/Could not parse request id or status from email 4355/',
            $logWriter->events[0]['message']
        );
    }

    /**
     * Test process method with nothing to do
     */
    public function testProcessNoEmails()
    {
        // stub data
        $emails = [];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);

        // expectations
        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', [], false)
            ->once()
            ->andReturn($emails);

        $this->sut->process();
    }
}
