<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use Dvsa\Olcs\Api\Service\Ebsr\S3ProcessorFactory;
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
        $mockSl->expects('get')->with('config')->andReturn([
            'ebsr' => ['input_s3_bucket' => "test",'txc_consumer_role_arn' => 'test-arn-role-123456789'],
            'awsOptions' => ['region' => 'test']
        ]);

        $stsAssumeRoleResult = new \Aws\Result();
        $stsAssumeRoleResult['Credentials'] = [
            'AccessKeyId' => 'access_key_id',
            'SecretAccessKey' => 'secret_access_key',
            'SessionToken' => 'session_token',
        ];

        m::mock('overload:\Aws\Sts\StsClient')->expects('AssumeRole')->andReturn($stsAssumeRoleResult);

        $this->sut->__invoke($mockSl, S3Processor::class);
    }
}
