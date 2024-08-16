<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Aws\Credentials\CredentialsInterface;
use Aws\S3\S3Client;
use Dvsa\Olcs\Email\Transport\S3FileOptions;
use Dvsa\Olcs\Email\Transport\S3FileOptionsFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class S3FileOptionsFactoryTest
 *
 * @package Dvsa\OlcsTest\Email\Transport
 */
class S3FileOptionsFactoryTest extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    public function setUp(): void
    {
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
        $this->sut = new S3FileOptionsFactory();
    }

    public function testInvoke()
    {
        // Params
        $config = [
            'awsOptions' => [
                'region' => 'eu-west-1',
                'version' => 'latest',
                's3Options' => [
                    'roleArn' => 'test',
                    'roleSessionName' => 'test'
                ]
            ],
            'mail' => [
                'type' => \Dvsa\Olcs\Email\Transport\MultiTransport::class,
                'options' => [
                    'transport' => [
                        ['type' => 'SendMail'],
                        [
                            'type' => \Dvsa\Olcs\Email\Transport\S3File::class,
                            'options' => [
                                'bucket' => 'devapp-olcs-pri-olcs-autotest-s3',
                                'key' => '/olcs.da.nonprod.dvsa.aws/email'
                            ]
                        ],
                    ]
                ],
            ],
        ];
        $provider = \Mockery::mock(CredentialsInterface::class);
        // Mocks
        $this->sm->setService('config', $config);
        $this->sm->setService('S3Client', new S3Client([
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
            'credentials' => $provider
        ]));

        $s3Options = $this->sut->__invoke($this->sm, S3FileOptions::class);
        $this->assertInstanceOf(S3FileOptions::class, $s3Options);
    }
}
