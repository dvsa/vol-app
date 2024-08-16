<?php

namespace Dvsa\OlcsTest\AwsSdk\Factories;

use Aws\Sqs\SqsClient;
use Dvsa\Olcs\AwsSdk\Factories\SqsClientFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Aws\Credentials\CredentialsInterface;

class SqsClientFactoryTest extends TestCase
{
    protected $sm;

    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SqsClientFactory();

        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $this->sm = $sm;
    }

    /**
     * @dataProvider dpTestInvoke
     */
    public function testInvoke($sqsOptions)
    {
        // Params
        $config = [
            'companies_house_connection' => ['proxy' => 'proxy-url'],
            'awsOptions' => [
                'region' => 'eu-west-1',
                'version' => 'latest',
                $sqsOptions
            ]
        ];
        $provider = \Mockery::mock(CredentialsInterface::class);
        // Mocks
        $this->sm->setService('config', $config);

        /**
         * @var SqsClient $sqsClient
         */
        $sqsClient = $this->sut->__invoke($this->sm, SqsClient::class);

        $this->assertInstanceOf(SqsClient::class, $sqsClient);
    }

    public function dpTestInvoke()
    {
        return [
            [
                'with_sqs_options' => [
                    'sqsOptions' => [
                        'credentials' => [
                            'key' => 'some_key',
                            'secret' => 'some_secret',
                        ],
                    ]
                ],
                'without-sqs-options' => []
            ]
        ];
    }
}
