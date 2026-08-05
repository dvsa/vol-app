<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList::class)]
final class GetListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = GetList::create(['contactDetailsId' => 9999]);

        $this->assertEquals(9999, $sut->getContactDetailsId());
    }
}
