<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nysiis;

use Dvsa\Olcs\Api\Service\Nysiis\NysiisRestClient;
use Dvsa\Olcs\Api\Service\Nysiis\NysiisRestClientFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

final class NysiisRestClientFactoryTest extends MockeryTestCase
{
    /**
     * Tests client created properly
     */
    public function testInvokeValid(): void
    {
        $config = [
            'nysiis' => [
                'rest' => [
                    'uri' => 'address',
                    'options' => []
                ]
            ]
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $sut = new NysiisRestClientFactory();
        $this->assertInstanceOf(NysiisRestClient::class, $sut->__invoke($mockSl, NysiisRestClient::class));
    }

    /**
     * Tests exception thrown for missing config
     *
     * @param $config
     * @param $errorMsg
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invokeFailProvider')]
    public function testInvokeMissingConfig(mixed $config, mixed $errorMsg): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($errorMsg);

        $config = [
            'nysiis' => [
                'rest' => $config
            ]
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $sut = new NysiisRestClientFactory();
        $sut->__invoke($mockSl, NysiisRestClient::class);
    }

    /**
     * data provider for testing service creation failures
     */
    public static function invokeFailProvider(): \Iterator
    {
        yield [['uri' => 'address'], 'Missing nysiis rest client options'];
        yield [['options' => []], 'Missing nysiis rest client uri'];
    }
}
