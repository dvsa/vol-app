<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service;

use Dvsa\Olcs\Api\Service\CpmsResponseException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\CpmsResponseException::class)]
final class CpmsResponseExceptionTest extends MockeryTestCase
{
    public function testSetGet(): void
    {
        $mockResp = m::mock(\Laminas\Http\Response::class);

        $sut = new CpmsResponseException();
        $sut->setResponse($mockResp);

        $this->assertSame($mockResp, $sut->getResponse());
    }
}
