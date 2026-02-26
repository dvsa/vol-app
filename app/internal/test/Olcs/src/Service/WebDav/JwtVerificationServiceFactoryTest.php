<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationServiceFactory;
use Olcs\Service\WebDav\JwtVerificationService;
use Olcs\Service\WebDav\JwtVerificationServiceFactory;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(JwtVerificationServiceFactory::class)]
class JwtVerificationServiceFactoryTest extends MockeryTestCase
{
    // Same 2048-bit key used in JwtVerificationServiceTest
    protected const JWT_PRIVATE_KEY_BASE64 = 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2UUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktjd2dnU2pBZ0VBQW9JQkFRRE1odGphdWlIS0hVdm8KanRPbWxqL1Fna2hBd3ZDd3BDU1ZIMFZrRzZlWXRjRFpYaEE5OTQvT1UrQzJDTUlGU0pnWis5aGE0NGtlcTV1aApmazJ5cXEvYlNiT3crK2FaeFRLWWdKU3Zad2dFRCtGSEFrM1VXbzJoUlk0L2taRGJFaTlRYlpGbkJLMXZIdzNTClQ4Y21jbVZEUHVCZCtZY0QwWnB5SVBpd2xXZTc3dVJoK3J3eWhLYWlsNVJXdmd3alcwT0lHYlR5anB1YlZwdG4KZEdWOVF1MHZveW05aTVOWnRQWDFxZnFzUm1CZGtPV1cyb25MakxEdHpRNUJOczA2ODdwaHZwUnBWT0hwc3k4OAo1YUk3Qm41TEdiY25zVDJXZkpsWGZKUFJtbkpjenMwVndGclpVQWIwcU0zdFBEcGw3Yy9JRlNKemxwd1Nob3lCCmZYdzhmRGtIQWdNQkFBRUNnZ0VBQVE1Wk1Oa3Y3eW5iWjdLZ3RHK0pUc2I1cTc5aWUyWEJWcTM1U0lqakZFdnEKc3JtbHVzMXZuTEU5RlQ3VS8yL2VYVU9ySUQya2NvekN1SE1GNm9GRjhLd0t3QndFdFJNR0Q1VGJVdEcxTFJXRwp6d29TM25Pc09kQ0UxVWFsUzluRmp6T2QzWkFtMnVldFVlMGNUK1pRQlBQamRFdzNkZElZN2JQNG9BMVhBY2ZMCldLWjRCdU9XQWlBNit6bVJ5ZTVOSkM5bHZ4QmhiZmk5VFYwOFBlcHM4RkRxUVVqeE0xRWRCZGVnekpRSTZ6R3MKcEFuMkxNb0E3c0Vra2hMZHg0VVJxZmlMR2FpWjIzM0J5dDFqVmlhM1JUSkhaREJUdEkycENqYldNbExsTWtjawplbm4zb0hsc0ZTMkVhK0xVb2VnNFloZUkyTktVdlh2cFMxR0FqelBBMFFLQmdRRC9FWDZFbjlSKzhoT1J0KzNhCjQ5VFMzNEJVL3BzNG1FTmJ3OS9vbmV5L0VqL3BpU0IvOFdnU2tKTzlENXpUYlF0bk84VXozaElTV0txaDh5Z2YKNnhWQXdhT1hyajk5dXJQckZoeTVTYVFTV21iR2NlZDJZbDgyc3c4aDh5eVVwY1FhaGlod3VvV2VQZU1QYXVaVgpGWVllSmRtOU0rUWJVajFqemJtNmFOazFRd0tCZ1FETlJoZllKT0tmYi95S3V5eUZsYnJTbENMbnh2ZktNaDVVCmNrZ1EzMlU2VDR3V0MxOWdLeTkzM29jR2lwM2JRR0htSkQ5Tk00SGNkWHRJZnVhSGpNbCtwMDVjcEJ3VG9FcS8KZTdDYnZhb0lCd1lKL1N4aU8yc1F5NzI5aUdYN1JDYUw5SXB0UGhGSFJSZkg4eUlYQXpmVUZDaGhZNDJLc3llVQpnTUhjcGE3TzdRS0JnQll0TFRUV3VLRDQ1eDZxUVRIZzBTWXNiSG4zVVFPUXFYcVhBeWEwWkZzUWVTSVMzQktECnFLckVpelBLNGJXNEsxK2tZTGJydmVKK2R3ZHhuckYzdlBkT1hxelhaNG5FNjlPcXJvQ2xtSEJJRDl0OUY4VEIKTk1vS3Myd2VPbWdLS1l5czBXTkx0RVpYMXlBR0NWU29kR3EybThISmV6R3M2czE4bTROTGptY0ZBb0dBSlFxSwo3dlBvK1FCS1AvWjZtRGhtbCswblIwKytFdnhzUUt6R21GVFhmV2d4VFNFTU90eFFHbjlMT2tEMUwwVTA1VVNSCkw2c2x1ZFJ4UktteGk3QTZBK0xJM3lxMTdreTBjRTB2bDByb1RiNkd0bks2K1pialFRcWkySHF1ZkdMVjJkZHMKeXoyeC9IeFpTRGwxWTFXdlUxTzMzYXNMTllZU2xGZVBvL001MGhrQ2dZRUFyR0o2WW1oVVl0RVdib3A1NkRzVwpoQXJOa0ZZdHUxN3lTaE1XWlQxbDU5VkJTY0wwbUFsRmF4OHdWL0pERXdjc2ZNaVdMelhhdDU3YXVZYlFGcGVtCjZ3MW8wbUM5VkhiRXlxRmZlc3BmNStQY2RiOU1zdHpaMnczcGJRWENXRUF2WFpHcWg5bmZBeCs3aFZJbEJXWjkKYzY4YXdFZXBja1RZaWMyMmZKVzdIeVU9Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K';

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsJwtVerificationServiceInstance(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with('Config')
            ->willReturn([
                WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
                    WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => self::JWT_PRIVATE_KEY_BASE64,
                ],
            ]);

        $factory = new JwtVerificationServiceFactory();
        $result = $factory($container, JwtVerificationService::class);

        $this->assertInstanceOf(JwtVerificationService::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenPrivateKeyMissing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with('Config')
            ->willReturn([
                WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE => [
                    WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY => '',
                ],
            ]);

        $factory = new JwtVerificationServiceFactory();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('WebDAV private key is not configured or invalid');

        $factory($container, JwtVerificationService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigNamespaceMissing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with('Config')
            ->willReturn([]);

        $factory = new JwtVerificationServiceFactory();

        $this->expectException(\InvalidArgumentException::class);

        $factory($container, JwtVerificationService::class);
    }
}
