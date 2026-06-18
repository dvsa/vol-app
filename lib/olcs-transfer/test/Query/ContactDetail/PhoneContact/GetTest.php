<?php

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\Get;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\Get
 */
class GetTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = Get::create(['id' => 123456]);

        static::assertEquals(123456, $sut->getId());
    }
}
