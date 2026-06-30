<?php

namespace CommonTest\Service\Api;

use Common\Service\Api\AbstractFactory;
use Laminas\Authentication\Storage\Session;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Request;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

class AbstractFactoryTest extends MockeryTestCase
{
    /** @var AbstractFactory | m\MockInterface */
    protected $sut;

    /** @var m\MockInterface | ContainerInterface */
    protected $mockSl;

    /** @var m\MockInterface | Request */
    protected $mockRequest;

    /** @var m\MockInterface | Translator */
    protected $mockTranslator;

    /**
     * @var Session|m\LegacyMockInterface|m\MockInterface
     */
    private $mockSession;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new AbstractFactory();

        $this->mockSl = m::mock(ContainerInterface::class);

        $this->mockRequest = m::mock(Request::class);

        $this->mockTranslator = m::mock(Translator::class);

        $this->mockSession = m::mock(Session::class);
    }

    /**
     * @dataProvider dpTestCanCreate
     */
    public function testCanCreate($requestedName, $expect): void
    {
        static::assertEquals($expect, $this->sut->canCreate($this->mockSl, $requestedName));
    }

    public function dpTestCanCreate(): array
    {
        return [
            [
                'requestedName' => 'Olcs\\RestService\\Backend\\Task',
                'expect' => true,
            ],
            [
                'requestedName' => 'Data\\Service\\Backend\\Task',
                'expect' => false,
            ],
        ];
    }

    public function testInvoke(): void
    {
        $config['service_api_mapping']['endpoints']['backend'] = 'http://olcs-backend';

        $this->mockTranslator->shouldReceive('getLocale')->withNoArgs()->andReturn('en-ts');

        $this->mockRequest->shouldReceive('getCookie')->andReturn(new Cookie(['secureToken' => 'abad1dea']));

        $this->mockSession->shouldReceive('read')->andReturn(['AccessToken' => 'abc123']);

        $this->mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $this->mockSl->shouldReceive('get')->with('translator')->andReturn($this->mockTranslator);
        $this->mockSl->shouldReceive('get')->with('Request')->andReturn($this->mockRequest);
        $this->mockSl->shouldReceive('get')->with(Session::class)->andReturn($this->mockSession);

        $client = ($this->sut)($this->mockSl, 'Olcs\RestService\TaskType');
        $this->assertEquals('olcs-backend', $client->url->getHost());
        $this->assertEquals('/task-type', $client->url->getPath());
        $this->assertEquals('en-ts', $client->getLanguage());
    }

    public function testInvokeInvalidMapping(): void
    {
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('No endpoint defined for: NoService');

        $config['service_api_mapping']['endpoints']['backend'] = 'http://olcs-backend';

        $this->mockSl->expects('get')->with('Config')->andReturn($config);

        ($this->sut)($this->mockSl, 'Olcs\RestService\NoService\TaskType');
    }

    public function testInvokeAdditionalEndpointConfig(): void
    {
        $config['service_api_mapping']['endpoints']['myapi'] = [
            'url' => 'https://external-api',
            'options' => [
                'sslcapath' => '/etc/ssl/certs',
                'sslverifypeer' => false,
            ],
            'auth' => [
                'username' => 'foo',
                'password' => 'bar',
            ],
        ];

        $this->mockTranslator->shouldReceive('getLocale')->withNoArgs()->andReturn('en-ts');

        $this->mockRequest->shouldReceive('getCookie')->andReturn(new Cookie(['secureToken' => 'abad1dea']));

        $this->mockSession->shouldReceive('read')->andReturn(['AccessToken' => 'abc123']);

        $this->mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $this->mockSl->shouldReceive('get')->with('translator')->andReturn($this->mockTranslator);
        $this->mockSl->shouldReceive('get')->with('Request')->andReturn($this->mockRequest);
        $this->mockSl->shouldReceive('get')->with(Session::class)->andReturn($this->mockSession);

        $client = ($this->sut)($this->mockSl, 'myapi\\some-resource');
        $this->assertEquals('external-api', $client->url->getHost());
        $this->assertEquals('/some-resource', $client->url->getPath());
        $this->assertEquals('en-ts', $client->getLanguage());
    }
}
