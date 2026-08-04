<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth\Adapter;

use Common\Auth\Adapter\CommandAdapter;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Common\Test\MocksServicesTrait;
use Laminas\Authentication\Result;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Mockery as m;

/**
 * Class CommandAdapterTest
 * @see CommandAdapter
 */
final class CommandAdapterTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var CommandAdapter
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsFailureWhenCommandReturnsNotOk(): void
    {
        // Setup
        $cmdResult = [
            'messages' => [
                'failed'
            ]
        ];

        $response = $this->response(false, $cmdResult);
        $commandSender = $this->commandSender($response);

        $sut = $this->setupSut($commandSender);
        $sut->setIdentity('username');
        $sut->setCredential('password');

        // Execute
        $result = $sut->authenticate();

        //Assert
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertEquals($cmdResult['messages'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('commandResultDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsResultObjectFromCommandResult(int $code, ?array $identity, ?array $messages): void
    {
        // Setup
        $cmdResult = [
            'flags' => [
                'code' => $code,
                'identity' => $identity,
                'messages' => $messages
            ]
        ];

        $response = $this->response(true, $cmdResult);
        $commandSender = $this->commandSender($response);

        $sut = $this->setupSut($commandSender);
        $sut->setIdentity('username');
        $sut->setCredential('password');

        // Execute
        $result = $sut->authenticate();

        //Assert
        $this->assertEquals($code, $result->getCode());
        $this->assertEquals($identity, $result->getIdentity());
        $this->assertEquals($messages, $result->getMessages());
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | int)>>
     *
     * @psalm-return array{'with id and messages': array{code: 1, identity: array{id: 1}, messages: list{'message'}}, 'with id and mo messages': array{code: 1, identity: array{id: 1}, messages: array<never, never>}, 'with messages and no id': array{code: 1, identity: array<never, never>, messages: list{'message'}}}
     */
    public static function commandResultDataProvider(): \Iterator
    {
        yield 'with id and messages' => [
            'code' => 1,
            'identity' => [
                'id' => 1
            ],
            'messages' => [
                'message'
            ]
        ];
        yield 'with id and mo messages' => [
            'code' => 1,
            'identity' => [
                'id' => 1
            ],
            'messages' => []
        ];
        yield 'with messages and no id' => [
            'code' => 1,
            'identity' => [],
            'messages' => [
                'message'
            ]
        ];
    }

    protected function response(bool $isOk, array $result): MockInterface|Response
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->andReturn($isOk);
        $mockResponse->shouldReceive('getResult')
            ->andReturn($result);

        return $mockResponse;
    }

    protected function commandSender(Response $response): MockInterface|CommandSender
    {
        $mockSender = m::mock(CommandSender::class);
        $mockSender->shouldReceive('send')
            ->andReturn($response);

        return $mockSender;
    }

    protected function setupSut(CommandSender $commandSender): CommandAdapter
    {
        return new CommandAdapter($commandSender);
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService('CommandSender', m::mock(CommandSender::class));
        return $serviceManager;
    }
}
