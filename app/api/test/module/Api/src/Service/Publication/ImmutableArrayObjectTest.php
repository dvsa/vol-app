<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication;

use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject
 */
class ImmutableArrayObjectTest extends MockeryTestCase
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
        static::assertEquals('0', $sut->offsetGet(888));

        $sut->offsetUnset(777);
        static::assertEquals('1', $sut->offsetGet(777));

        $sut->exchangeArray([666 => 'NEW VAL']);
        static::assertEquals($data, $sut->getArrayCopy());
    }
}
