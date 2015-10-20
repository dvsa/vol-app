<?php

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\BatchInspectionRequestEmailProcessingService;
use Common\BusinessService\Response;

/**
 * Test Batch Processing Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BatchInspectionRequestEmailProcessingServiceTest extends MockeryTestCase
{
    protected $sm;

    /**
     * @var BatchInspectionRequestEmailProcessingService
     */
    protected $sut;
    protected $logWriter;
    protected $mockConsole;

    public function setUp()
    {
        Bootstrap::setupLogger();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new BatchInspectionRequestEmailProcessingService();
        $this->sut->setServiceLocator($this->sm);

        $this->mockConsole = m::mock('\Zend\Console\Adapter\Posix');

        $this->logWriter = Logger::getLogger()->getWriters()->toArray()[0];
    }

    /**
     * Test logging - message should go to logger and console
     */
    public function testLogging()
    {
        $this->sut->setConsoleAdapter($this->mockConsole);

        // expectations
        $this->mockConsole
            ->shouldReceive('writeLine')
            ->once()
            ->with('message');

        $this->sut->log('message');

        // assertions
        $this->assertEquals('message', $this->logWriter->events[0]['message']);
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
            'subject' => '[ Maintenance Inspection ] REQUEST=23457,STATUS=U',
        ];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('BusinessServiceManager', $bsm);
        $mockBusinessService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm->setService('InspectionRequestUpdate', $mockBusinessService);

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

        $mockBusinessService
            ->shouldReceive('process')
            ->with(['id' => '23456', 'status'=> 'S'])
            ->once()
            ->andReturn(new Response(Response::TYPE_SUCCESS))
            ->shouldReceive('process')
            ->with(['id' => '23457', 'status'=> 'U'])
            ->once()
            ->andReturn(new Response(Response::TYPE_FAILED));

        // should only delete on success
        $mockRestHelper
            ->shouldReceive('makeRestCall')
            ->with('email\inspection-request', 'DELETE', ['id' => '4355'], null)
            ->once();

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

        $this->assertRegexp('/Could not retrieve email 4355/', $this->logWriter->events[0]['message']);
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
            '/Unable to parse email subject line: spam!/',
            $this->logWriter->events[0]['message']
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

    /**
     * Test process method handles http client error
     */
    public function testProcessError()
    {
        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);

        // expectations
        $mockRestHelper
            ->shouldReceive('sendGet')
            ->with('email\\inspection-request', [], false)
            ->andThrow(new \Zend\Http\Client\Exception\RuntimeException('fail'));

        $this->sut->process();

        // assertions
        $this->assertEquals('Error: fail', $this->logWriter->events[0]['message']);
    }

    /**
     * Test process method
     */
    public function testProcessNotFound()
    {
        // stub data
        $emails = [
            1 => '4355',
        ];
        $email1 = [
            'subject' => '[ Maintenance Inspection ] REQUEST=23456,STATUS=S',
        ];

        // mocks
        $mockRestHelper = m::mock();
        $this->sm->setService('Helper\Rest', $mockRestHelper);
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('BusinessServiceManager', $bsm);
        $mockBusinessService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm->setService('InspectionRequestUpdate', $mockBusinessService);

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

        $mockBusinessService
            ->shouldReceive('process')
            ->with(['id' => '23456', 'status'=> 'S'])
            ->once()
            ->andReturn(new Response(Response::TYPE_NOT_FOUND));

         $this->sut->process();

        // assertions
        $expectedLogMessage = '==Unable to find the inspection request from the email subject line: '
            .'[ Maintenance Inspection ] REQUEST=23456,STATUS=S';
        $this->assertEquals($expectedLogMessage, $this->logWriter->events[0]['message']);
    }
}
