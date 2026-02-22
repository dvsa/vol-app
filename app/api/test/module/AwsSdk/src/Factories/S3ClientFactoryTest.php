<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\AwsSdk\Factories;

use Aws\Credentials\CredentialsInterface;
use Aws\S3\S3Client;
use Dvsa\Olcs\AwsSdk\Factories\S3ClientFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class S3ClientTest
 *
 * @package Dvsa\OlcsTest\AwsSdk
 */
class S3ClientFactoryTest extends TestCase
{
    protected $sm;

    protected $sut;

    public function setUp(): void
    {
        $this->sut = new S3ClientFactory();

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

    public function testInvoke(): void
    {
        // Params
        $config = [
            'awsOptions' => [
                'region' => 'eu-west-1',
                'version' => 'latest'
            ]
        ];
        $provider = \Mockery::mock(CredentialsInterface::class);
        // Mocks
        $this->sm->setService('config', $config);
        $this->sm->setService('S3Client', new S3Client([
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
            'credentials' => $provider
        ]));
        /**
         * @var S3Client
         */
        $s3Options = $this->sut->__invoke($this->sm, S3Client::class);
        $this->assertInstanceOf(S3Client::class, $s3Options);
    }
}
