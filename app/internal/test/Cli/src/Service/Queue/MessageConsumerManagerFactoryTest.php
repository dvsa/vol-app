<?php

/**
 * Message Consumer Manager Factory Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CliTest\Service\Queue;

use PHPUnit_Framework_TestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Queue\MessageConsumerManagerFactory;

/**
 * Message Consumer Manager Factory Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MessageConsumerManagerFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = new MessageConsumerManagerFactory();

        $this->sm = Bootstrap::getServiceManager();
    }

    public function testCreateService()
    {
        // Params
        $config = [
            'message_consumer_manager' => [
                'invokables' => [
                    'foo' => '\stdClass'
                ]
            ]
        ];

        // Mocks
        $this->sm->setService('Config', $config);

        $brm = $this->sut->createService($this->sm);

        $this->assertInstanceOf('\Cli\Service\Queue\MessageConsumerManager', $brm);
        $this->assertSame($this->sm, $brm->getServiceLocator());
        $this->assertTrue($brm->has('foo'));
        $this->assertFalse($brm->has('bar'));
    }
}
