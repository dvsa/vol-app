<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Service\ClientFactory;
use Dvsa\Olcs\DocumentShare\Service\WebDavClient;
use Laminas\Log\Logger;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ClientFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = [
            'document_share' => [
                'http' => [],
                'client' => [
                    'workspace' => 'testwebdav',
                    'username' => 'testwebdav',
                    'password' => 'ttestwebdavest',
                    'webdav_baseuri' => 'http://testdocument_share',
                ]
            ]
        ];

        $sut = new ClientFactory();

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Configuration')->andReturn($config);
        $mockContainer->expects('get')->with('Logger')->andReturn($this->createStub(\Dvsa\OlcsTest\SafeLogger::class));

        $service = $sut->__invoke($mockContainer, WebDavClient::class);
        $this->assertInstanceOf(WebDavClient::class, $service);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProvideMissingConfig')]
    public function testMissingConfigExceptions(array $config, string $exceptionMessage): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $sut = new ClientFactory();
        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Configuration')->andReturn($config);
        $sut->__invoke($mockContainer, WebDavClient::class);
    }

    public static function dpProvideMissingConfig(): array
    {
        $configWebDavMissingHttpOption = [
            'document_share' => [
                'client' => []
            ]
        ];

        $configWebDavMissingClientOption = [
            'document_share' => [
                'http' => []
            ]
        ];

        $configWebDavMissingUsername = [
            'document_share' => [
                'http' => [],
                'client' => [
                    'webdav_baseuri' => 'http://testdocument_share',
                    'workspace' => 'test',
                    'password' => 'test'
                ]
            ]
        ];

        $configWebDavMissingPassword = [
            'document_share' => [
                'http' => [],
                'client' => [
                    'webdav_baseuri' => 'http://testdocument_share',
                    'workspace' => 'test',
                    'username' => 'test'
                ]
            ]
        ];

        $webDavConfigMissingBaseUri = [
            'document_share' => [
                'http' => [],
                'client' => [
                    'workspace' => 'testwebdav',
                    'username' => 'testwebdav',
                    'password' => 'ttestwebdavest',
                ]
            ]
        ];

        $webDavConfigMissingWorkspace = [
            'document_share' => [
                'http' => [],
                'client' => [
                    'username' => 'testwebdav',
                    'password' => 'ttestwebdavest',
                    'webdav_baseuri' => 'http://testdocument_share',
                ]
            ]
        ];

        return [
            "OptionsMissingHttp" => [
                $configWebDavMissingHttpOption,
                'Options could not be found in "document_share.http',
            ],
            "OptionsMissingClient" => [
                $configWebDavMissingClientOption,
                'Options could not be found in "document_share.client',
            ],
            "WebDavConfigMissingUsername" => [
                $configWebDavMissingUsername,
                'Missing required option document_share.client.username',
            ],
            "WebDavConfigMissingPassword" => [
                $configWebDavMissingPassword,
                'Missing required option document_share.client.password',
            ],
            "WebDavConfigMissingWorkspace" => [
                $webDavConfigMissingWorkspace,
                'Missing required option document_share.client.workspace',
            ],
            "WebDavConfigMissingPWebDavBaseUri" => [
                $webDavConfigMissingBaseUri,
                'Missing required option document_share.client.webdav_baseuri',
            ]
        ];
    }
}
