<?php

/**
 * Test FlashMessengerTrait
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */

namespace CommonTest\Util;

use Laminas\Http\Client as HttpClient;
use Laminas\Http\Header\Accept;
use Laminas\Http\Header\AcceptLanguage;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\ParametersInterface;
use Laminas\Uri\Http as HttpUri;
use Common\Util\ResponseHelper;
use Common\Util\RestClient;
use Mockery as m;

/**
 * Test FlashMessengerTrait
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */
class RestClientTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public $handleReponseMethods = [
        'checkForValidResponseBody',
        'checkForInternalServerError',
        'checkForUnexpectedResponseCode'
    ];

    #[\Override]
    public function setUp(): void
    {
        // Upstream wont fix this, so we need to suppress the deprecation - https://github.com/laminas/laminas-stdlib/issues/85
        set_error_handler(function ($severity, $message, $file, $line) {
            if (strpos($message, 'Serializable') !== false) {
                return true;
            }
            return false;
        }, E_DEPRECATED);
    }

    public function getSutMock(array|null $methods = null): RestClient
    {
        if ($methods === null) {
            $methods = [];
        }

        return $this->createPartialMock(RestClient::class, $methods);
    }

    public function testUrl(): void
    {
        $mock = $this->getSutMock(['pathOrParams']);
        $mock->expects($this->once())
            ->method('pathOrParams')
            ->with('/licence');
        $toString = $this->createPartialMock(HttpUri::class, ['toString']);
        $toString->expects($this->once())
            ->method('toString');
        $mock->url = $toString;
        $mock->url('/licence');
    }

    public function testCreate(): void
    {
        $mock = $this->getSutMock(['post']);
        $mock->expects($this->once())
            ->method('post')
            ->with('/licence', ['id' => 7]);
        $mock->create('/licence', ['id' => 7]);
    }

    public function testPost(): void
    {
        $mock = $this->getSutMock(['request']);

        $mock->expects($this->once())
            ->method('request')
            ->with('POST', null, ['id' => 7]);

        $mock->post(null, ['id' => 7]);
    }

    public function testRead(): void
    {
        $mock = $this->getSutMock(['get']);

        $mock->expects($this->once())
            ->method('get')
            ->with(null, ['id' => 7]);

        $mock->read(null, ['id' => 7]);
    }

    public function testGet(): void
    {
        $mock = $this->getSutMock(['request']);

        $mock->expects($this->once())
            ->method('request')
            ->with('GET', '/licence', []);

        $mock->get('licence', []);
    }

    public function testUpdate(): void
    {
        $mock = $this->getSutMock(['put']);

        $mock->expects($this->once())
            ->method('put')
            ->with(null, ['id' => 7]);

        $mock->update(null, ['id' => 7]);
    }

    public function testPut(): void
    {
        $mock = $this->getSutMock(['request']);

        $mock->expects($this->once())
            ->method('request')
            ->with('PUT', null, ['id' => 7]);

        $mock->put(null, ['id' => 7]);
    }

    public function testPatch(): void
    {
        $mock = $this->getSutMock(['request']);

        $mock->expects($this->once())
            ->method('request')
            ->with('PATCH', null, ['id' => 7]);

        $mock->patch(null, ['id' => 7]);
    }

    public function testDelete(): void
    {
        $mock = $this->getSutMock(['request']);

        $mock->expects($this->once())
            ->method('request')
            ->with('DELETE', null, ['id' => 7]);

        $mock->delete(null, ['id' => 7]);
    }

    public function testRequest(): void
    {
        $mock = $this->getSutMock(['prepareRequest', 'getResponseHelper']);

        $mock->expects($this->once())
            ->method('prepareRequest')
            ->with('GET', 'licence', ['id' => 7]);

        $httpResponse = m::mock(Response::class);

        $send = $this->createPartialMock(HttpClient::class, ['send']);
        $send->expects($this->once())
            ->method('send')
            ->will($this->returnValue($httpResponse));
        $mock->client = $send;

        $responseHelper = $this->createPartialMock(
            ResponseHelper::class,
            ['setMethod', 'setResponse', 'setParams', 'handleResponse']
        );
        $responseHelper->expects($this->once())
            ->method('setMethod')
            ->with('GET');
        $responseHelper->expects($this->once())
            ->method('setResponse')
            ->with($httpResponse);
        $responseHelper->expects($this->once())
            ->method('setParams')
            ->with(['id' => 7]);
        $responseHelper->expects($this->once())
            ->method('handleResponse');

        $mock->expects($this->once())
            ->method('getResponseHelper')
            ->will($this->returnValue($responseHelper));

        $mock->request('GET', 'licence', ['id' => 7]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetResponseHelper(): void
    {
        $mock = $this->getSutMock(null);
        $mock->getResponseHelper();
    }

    public function testPrepareRequest(): void
    {
        $mock = $this->getSutMock(['getClientRequest', 'getAccept', 'getAcceptLanguage']);

        $mock->setAuthHeader('auth_header');

        $accept = $this->createPartialMock(Accept::class, ['addMediaType']);
        $accept->expects($this->once())
            ->method('addMediaType')
            ->with('application/json');

        $mock->expects($this->once())
            ->method('getAccept')
            ->will($this->returnValue($accept));

        $acceptLanguage = $this->createPartialMock(AcceptLanguage::class, ['addLanguage']);
        $acceptLanguage->expects($this->once())
            ->method('addLanguage')
            ->with('en-gb');

        $mock->expects($this->once())
            ->method('getAcceptLanguage')
            ->will($this->returnValue($acceptLanguage));

        $client = $this->createPartialMock(
            HttpClient::class,
            [
                'setRequest', 'setUri', 'setHeaders', 'setMethod', 'setEncType', 'setRawBody', 'getRequest',
                'resetParameters'
            ]
        );

        $request = m::mock(Request::class);

        $client->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $client->expects($this->once())
            ->method('setUri')
            ->with('licence');

        $toString = $this->createPartialMock(HttpUri::class, ['toString']);
        $toString->expects($this->once())
            ->method('toString');
        $mock->url = $toString;

        $client->expects($this->once())
            ->method('setHeaders')
        ->with([$accept, $acceptLanguage, null, 'auth_header']);
        $client->expects($this->once())
            ->method('setMethod')
            ->with('POST');
        $client->expects($this->once())
            ->method('setEncType')
            ->with('application/json');
        $client->expects($this->once())
            ->method('setRawBody')
            ->with(json_encode(['id' => 7]));
        $mock->client = $client;

        $mock->expects($this->once())
            ->method('getClientRequest')
            ->will($this->returnValue($request));

        $client->expects($this->once())
             ->method('resetParameters');

        $mock->prepareRequest('POST', 'licence', ['id' => 7]);
    }

    /**
     * @NOTE I duplicate most of the above method just to get the coverage,
     *  These tests need attention, but that is out of scope in my story
     */
    public function testPrepareGetRequest(): void
    {
        $mock = $this->getSutMock(['getClientRequest', 'getAccept', 'getAcceptLanguage']);

        $accept = $this->createPartialMock(Accept::class, ['addMediaType']);
        $accept->expects($this->once())
            ->method('addMediaType')
            ->with('application/json');

        $mock->expects($this->once())
            ->method('getAccept')
            ->will($this->returnValue($accept));

        $acceptLanguage = $this->createPartialMock(AcceptLanguage::class, ['addLanguage']);
        $acceptLanguage->expects($this->once())
            ->method('addLanguage')
            ->with('en-gb');

        $mock->expects($this->once())
            ->method('getAcceptLanguage')
            ->will($this->returnValue($acceptLanguage));

        $client = $this->createPartialMock(
            HttpClient::class,
            [
                'setRequest',
                'setUri',
                'setHeaders',
                'setMethod',
                'getRequest',
                'resetParameters'
            ]
        );

        $request = m::mock(Request::class);
        $parameters = m::mock(ParametersInterface::class);

        $client->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $client->expects($this->once())
            ->method('setUri')
            ->with('licence');

        $toString = $this->createPartialMock(HttpUri::class, ['toString']);
        $toString->expects($this->once())
            ->method('toString');
        $mock->url = $toString;

        $client->expects($this->once())
            ->method('setHeaders');
        $client->expects($this->once())
            ->method('setMethod')
            ->with('GET');

        $client->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $request->expects('getQuery')->andReturn($parameters);
        $parameters->expects('fromArray')
            ->with(['id' => 7]);

        $mock->client = $client;

        $mock->expects($this->once())
            ->method('getClientRequest')
            ->will($this->returnValue($request));

        $mock->prepareRequest('GET', 'licence', ['id' => 7]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetAccept(): void
    {
        $mock = $this->getSutMock(null);
        $mock->getAccept();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetClientRequest(): void
    {
        $mock = $this->getSutMock(null);
        $mock->getClientRequest();
    }

    public function testGetLanguage(): void
    {
        $mock = $this->getSutMock(null);
        $this->assertEquals('en-gb', $mock->getLanguage());
    }

    public function testSetLanguage(): void
    {
        $mock = $this->getSutMock(null);
        $mock->setLanguage('cy_cy');
        $this->assertEquals('cy-cy', $mock->getLanguage());
    }

    public function testGetAcceptLanguage(): void
    {
        $sut = new RestClient(new HttpUri());
        $acceptLanguage = $sut->getAcceptLanguage();

        $this->assertInstanceOf(\Laminas\Http\Header\AcceptLanguage::class, $acceptLanguage);
    }

    public function testConstructorWithParams(): void
    {
        $options = [
            'foo' => 'bar',
        ];
        $auth = [
            'username' => 'test',
            'password' => 'secret',
        ];

        $sut = new RestClient(new HttpUri(), $options, $auth);

        $config = $sut->client->getAdapter()->getConfig();

        // check our config is set
        $this->assertEquals('bar', $config['foo']);

        // check previous default options are still set
        $this->assertTrue($config['keepalive']);
        $this->assertEquals(30, $config['timeout']);
    }
}
