<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication;

use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject::class)]
final class ImmutableArrayObjectTest extends MockeryTestCase
{
    public function testOffset(): void
    {
        $data = [
            888 => '0',
            777 => '1',
            666 => '2',
        ];
        $sut = new ImmutableArrayObject($data);

        $sut->offsetSet(888, 999);
        $this->assertEquals('0', $sut->offsetGet(888));

        $sut->offsetUnset(777);
        $this->assertEquals('1', $sut->offsetGet(777));

        $sut->exchangeArray([666 => 'NEW VAL']);
        $this->assertEquals($data, $sut->getArrayCopy());
    }
}
