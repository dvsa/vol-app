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
        $this->markTestIncomplete();
    }
}
