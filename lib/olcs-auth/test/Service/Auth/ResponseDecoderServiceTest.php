<?php

/**
 * Response Decoder Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\Exception\RuntimeException;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;

/**
 * Response Decoder Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResponseDecoderServiceTest extends MockeryTestCase
{
    public function testDecodeFailed(): void
    {
        $this->expectException(RuntimeException::class);

        $response = new Response();
        $response->setContent('{"foo":\'var\'}');

        $sut = new ResponseDecoderService();
        $sut->decode($response);
    }

    public function testDecode(): void
    {
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"foo": "bar"}');

        $sut = new ResponseDecoderService();
        $result = $sut->decode($response);

        $this->assertEquals(['foo' => 'bar', 'status' => 200], $result);
    }
}
