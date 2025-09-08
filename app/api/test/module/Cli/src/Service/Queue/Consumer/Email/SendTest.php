<?php

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendUserRegistered as SampleEmail;
use Dvsa\Olcs\Api\Domain\Command\Queue\Complete;
use Dvsa\Olcs\Api\Domain\Command\Queue\Failed;
use Dvsa\Olcs\Api\Domain\Command\Queue\Retry;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Email\Send;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Laminas\Serializer\Adapter\Json as LaminasJson;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * @covers \Dvsa\Olcs\Cli\Service\Queue\Consumer\Email\Send
 * @covers \Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer
 */
class SendTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Send::class;

    /** @var  Send */
    protected $sut;

    public function testProcessMessageSuccess()
    {
        $json = new LaminasJson();
        $options = $json->serialize(
            [
                'commandClass' => SampleEmail::class,
                'commandData' => [
                    'user' => 1,
                ]
            ]
        );

        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions($options);

        $expectedDtoData = ['user' => 1];
        $cmdResult = new Result();
        $cmdResult
            ->addMessage('Email sent');

        $this->expectCommand(
            SampleEmail::class,
            $expectedDtoData,
            $cmdResult
        );

        $this->expectCommand(
            Complete::class,
            ['item' => $item],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Successfully processed message: 99 ' . $options . ' Email sent',
            $result
        );
    }

    public function testProcessMessageHandlesEmailNotSentException()
    {
        $json = new LaminasJson();
        $options = $json->serialize(
            [
                'commandClass' => SampleEmail::class,
                'commandData' => [
                    'user' => 1,
                ]
            ]
        );

        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions($options);

        $message = 'Email not sent';

        $this->chm
            ->shouldReceive('handleCommand')
            ->with(SampleEmail::class)
            ->andThrow(new EmailNotSentException($message));

        $this->expectCommand(
            Retry::class,
            [
                'item' => $item,
                'retryAfter' => 900,
                'lastError' => $message,
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Requeued message: 99 ' . $options . ' for retry in 900 ' . $message,
            $result
        );
    }

    public function testProcessMessageHandlesLaminasServiceException()
    {
        $json = new LaminasJson();
        $options = $json->serialize(
            [
                'commandClass' => SampleEmail::class,
                'commandData' => [
                    'user' => 1,
                ]
            ]
        );

        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions($options);

        $message = 'Email not sent';

        $this->chm
            ->shouldReceive('handleCommand')
            ->with(SampleEmail::class)
            ->andThrow(new InvalidServiceException($message));

        $this->expectCommand(
            Failed::class,
            [
                'item' => $item,
                'lastError' => 'Email not sent',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 ' . $options . ' ' . $message,
            $result
        );
    }

    public function testProcessMessageHandlesException()
    {
        $json = new LaminasJson();
        $options = $json->serialize(
            [
                'commandClass' => SampleEmail::class,
                'commandData' => [
                    'user' => 1,
                ]
            ]
        );

        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions($options);

        $message = 'Email not sent';

        $this->chm
            ->shouldReceive('handleCommand')
            ->with(SampleEmail::class)
            ->andThrow(new \Exception($message));

        $this->expectCommand(
            Failed::class,
            [
                'item' => $item,
                'lastError' => 'Email not sent',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 ' . $options . ' ' . $message,
            $result
        );
    }

    public function testProcessMessageHandlesMaxAttempts()
    {
        $json = new LaminasJson();
        $options = $json->serialize(
            [
                'commandClass' => SampleEmail::class,
                'commandData' => [
                    'user' => 1,
                ]
            ]
        );

        $item = new QueueEntity();
        $item->setId(99);
        $item->setAttempts(100);
        $item->setOptions($options);

        $this->chm
            ->shouldReceive('handleCommand')
            ->never();

        $this->expectCommand(
            Failed::class,
            [
                'item' => $item,
                'lastError' => QueueEntity::ERR_MAX_ATTEMPTS,
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 ' . $options . ' ' . QueueEntity::ERR_MAX_ATTEMPTS,
            $result
        );
    }
}
