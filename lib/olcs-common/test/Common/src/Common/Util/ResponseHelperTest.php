<?php

/**
 * Test ResponseHelperTest
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Controller\Util;

use Laminas\Http\Response as HttpResponse;

/**
 * Test ResponseHelperTest
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class ResponseHelperTest extends \PHPUnit\Framework\TestCase
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

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSetResponse(): void
    {
        $mock = $this->createStub(\Common\Util\ResponseHelper::class);
        $response = new \Laminas\Http\Response();
        $mock->setResponse($response);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testGetResponse(): void
    {
        $mock = $this->createStub(\Common\Util\ResponseHelper::class);
        $mock->response = new \Laminas\Http\Response();
        $mock->getResponse();
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSetMethod(): void
    {
        $mock = $this->createStub(\Common\Util\ResponseHelper::class);
        $mock->setMethod('blah');
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSetParams(): void
    {
        $mock = $this->createStub(\Common\Util\ResponseHelper::class);
        $mock->setParams([1, 2, 3]);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSetData(): void
    {
        $mock = $this->createStub(\Common\Util\ResponseHelper::class);
        $mock->getData([1, 2, 3]);
    }

    public function testHandleResponseGet(): void
    {
        $mock = $this->getSutMock($this->handleReponseMethods);

        $response = $this->createPartialMock(HttpResponse::class, ['getBody', 'getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getBody')
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(201);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404);
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
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
            ->willReturn('{}');
        $mock->response = $response;

        $mock->expects($this->once())
            ->method('checkForValidResponseBody')
            ->with('{}');

        $mock->expects($this->once())
            ->method('checkForInternalServerError')
            ->with('{}');
        $mock->method = 'BLAH';
        $mock->handleResponse();
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
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
            ->willReturn(500);
        $mock->response = $response;
        $mock->checkForInternalServerError('{}');
    }

    public function testCheckForNoInternalServerError(): void
    {
        $mock = $this->getSutMock(null);
        $response = $this->createPartialMock(HttpResponse::class, ['getStatusCode']);
        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200);
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
            ->willReturn(500);
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
            ->willReturn(200);
        $mock->response = $response;
        $mock->method = 'GET';
        $mock->checkForUnexpectedResponseCode('{}');
    }
}
