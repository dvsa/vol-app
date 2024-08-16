<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Aws\S3\S3Client;
use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use Dvsa\Olcs\Api\Service\Ebsr\S3ProcessorFactory;
use Dvsa\Olcs\Api\Service\File\FileUploaderInterface as FileUploaderInterfaceAlias;
use Laminas\Config\Config;
use Mockery as m;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property S3ProcessorFactory $sut
 */
class S3ProcessorFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    private S3ProcessorFactory $sut;

    public function setUp(): void
    {
        $this->sut = new S3ProcessorFactory();
        parent::setUp();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvoke(): void
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([
            'ebsr' => ['input_s3_bucket' => "test",'txc_consumer_role_arn' => 'test-arn-role-123456789'],
            'awsOptions' => ['region' => 'test']
        ]);
        $mockClient = m::mock(S3Client::class);
        $mockConfig = m::mock(Config::class);
        $mockFileUploader = m::mock(FileUploaderInterfaceAlias::class);

        $stsAssumeRoleResult = new \Aws\Result();
        $stsAssumeRoleResult['Credentials'] = [
            'AccessKeyId' => 'access_key_id',
            'SecretAccessKey' => 'secret_access_key',
            'SessionToken' => 'session_token',
        ];

        m::mock('overload:\Aws\Sts\StsClient')->shouldReceive('AssumeRole')->andReturn($stsAssumeRoleResult);
        $mockLogger = m::mock(\Laminas\Log\Logger::class);
        $mockSl->shouldReceive('get')->with('S3Client')->andReturn($mockClient);
        $mockSl->shouldReceive('get')->with('FileUploader')->andReturn($mockFileUploader);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($mockConfig);
        $mockSl->shouldReceive('get')->with('Logger')->andReturn($mockLogger);
        $this->assertInstanceOf(S3Processor::class, $this->sut->__invoke($mockSl, S3Processor::class));
    }
}
