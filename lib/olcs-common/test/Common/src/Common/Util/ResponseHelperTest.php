<?php

/**
 * Test ResponseHelperTest
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */

namespace CommonTest\Controller\Util;

use Laminas\Http\Response as HttpResponse;

/**
 * Test ResponseHelperTest
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */
class ResponseHelperTest extends \PHPUnit\Framework\TestCase
{
    public $handleReponseMethods = [
        'checkForValidResponseBody',
        'checkForInternalServerError',
        'checkForUnexpectedResponseCode'
    ];

    public function getSutMock(array|null $methods = []): \Common\Util\ResponseHelper
    {
        if ($methods === null) {
            $methods = [];
        }

        return $this->createPartialMock(
            \Common\Util\ResponseHelper::class,
            $methods
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSetResponse(): void
    {
        $mock = $this->createMock(\Common\Util\ResponseHelper::class);
        $response = new \Laminas\Http\Response();
        $mock->setResponse($response);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetResponse(): void
    {
        $mock = $this->createMock(\Common\Util\ResponseHelper::class);
        $mock->response = new \Laminas\Http\Response();
        $mock->getResponse();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSetMethod(): void
    {
        $mock = $this->createMock(\Common\Util\ResponseHelper::class);
        $mock->setMethod('blah');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSetParams(): void
    {
        $mock = $this->createMock(\Common\Util\ResponseHelper::class);
        $mock->setParams([1, 2, 3]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSetData(): void
    {
        $mock = $this->createMock(\Common\Util\ResponseHelper::class);
        $mock->getData([1, 2, 3]);
    }

    public function testHandleResponseGet(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'GET';
        $mock->handleResponse();
    }

    public function testHandleInvalidResponseGet(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(404));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'GET';
        $mock->handleResponse();
    }

    public function testHandleResponsePost(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(201));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'POST';
        $mock->handleResponse();
    }

    public function testHandleInvalidResponsePost(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(404));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'POST';
        $mock->handleResponse();
    }

    public function testHandleResponsePut(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'PUT';
        $mock->handleResponse();
    }

    public function testHandleInvalidResponsePut(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(404));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'PUT';
        $mock->handleResponse();
    }

    public function testHandleResponseDelete(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'DELETE';
        $mock->handleResponse();
    }

    public function testHandleInvalidResponseDelete(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(404));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'DELETE';
        $mock->handleResponse();
    }

    public function testHandleInvalidResponseMethod(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->will($this->returnValue('{}'));
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'BLAH';
        $mock->handleResponse();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckForValidResponseBody(): void
    {
        $mock = $this->getSutMock([]);
        $mock->checkForValidResponseBody('{}');
    }

    public function testCheckForInvalidResponseBodyString(): void
    {
        $this->expectException(\Exception::class);

        $mock = $this->getSutMock(null);
        $mock->checkForValidResponseBody(55);
    }

    public function testCheckForInvalidResponseBodyJson(): void
    {
        $this->expectException(\Exception::class);

        $mock = $this->getSutMock(null);
        $mock->checkForValidResponseBody('blah');
    }

    public function testCheckForInternalServerError(): void
    {
        $this->expectException(\Exception::class);

        $mock = $this->getSutMock(null);
        $response = $this->createPartialMock(HttpResponse::class, ['getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(500));
        $mock->response = $response;
        $mock->checkForInternalServerError('{}');
    }

    public function testCheckForNoInternalServerError(): void
    {
        $mock = $this->getSutMock(null);
        $response = $this->createPartialMock(HttpResponse::class, ['getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $mock->response = $response;
        $mock->checkForInternalServerError('{}');
    }

    public function testCheckForUnexpectedResponseCode(): void
    {
        $this->expectException(\Exception::class);

        $mock = $this->getSutMock(null);
        $response = $this->createPartialMock(HttpResponse::class, ['getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(500));
        $mock->response = $response;
        $mock->method = 'GET';
        $mock->checkForUnexpectedResponseCode('{}');
    }

    public function testCheckForExpectedResponseCode(): void
    {
        $mock = $this->getSutMock(null);
        $response = $this->createPartialMock(HttpResponse::class, ['getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $mock->response = $response;
        $mock->method = 'GET';
        $mock->checkForUnexpectedResponseCode('{}');
    }
}
