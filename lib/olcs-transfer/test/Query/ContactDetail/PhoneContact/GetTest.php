<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\Get;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\Get::class)]
final class GetTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = Get::create(['id' => 123456]);

        $this->assertEquals(123456, $sut->getId());
    }
}
