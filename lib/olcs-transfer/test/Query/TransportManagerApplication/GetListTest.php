<?php

namespace Dvsa\OlcsTest\Transfer\Query\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList;

/**
 * Get List Test
 */
class GetListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = GetList::create(
            [
                'user' => 11,
                'application' => 12,
                'transportManager' => 13,
                'appStatuses' => 14,
                'filterByOrgUser' => true,
            ]
        );

        $this->assertEquals(11, $query->getUser());
        $this->assertEquals(12, $query->getApplication());
        $this->assertEquals(13, $query->getTransportManager());
        $this->assertEquals(14, $query->getAppStatuses());
        $this->assertEquals(15, $query->getFilterByOrgUser());
    }
}
