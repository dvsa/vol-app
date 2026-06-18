<?php

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList
 */
class GetListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = GetList::create(['contactDetailsId' => 9999]);

        static::assertEquals(9999, $sut->getContactDetailsId());
    }
}
