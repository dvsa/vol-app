<?php

namespace Dvsa\OlcsTest\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\Query\Fee\FeeList;

/**
 * Fee List Test
 */
class FeeListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = FeeList::create(
            [
                'application' => 11,
                'licence' => 12,
                'task' => 13,
                'busReg' => 14,
                'irfoGvPermit' => 15,
                'isMiscellaneous' => 1,
                'status' => 'current',
                'ids' => [1, 2, 3],
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );

        $this->assertEquals(11, $query->getApplication());
        $this->assertEquals(12, $query->getLicence());
        $this->assertEquals(13, $query->getTask());
        $this->assertEquals(14, $query->getBusReg());
        $this->assertEquals(15, $query->getIrfoGvPermit());
        $this->assertEquals(1, $query->getIsMiscellaneous());
        $this->assertEquals('current', $query->getStatus());
        $this->assertEquals([1, 2, 3], $query->getIds());

        $this->assertEquals(1, $query->getPage());
        $this->assertEquals(10, $query->getLimit());
        $this->assertEquals('id', $query->getSort());
        $this->assertEquals('ASC', $query->getOrder());

        $query->setPage(2);
        $query->setLimit(25);
        $query->setSort('invoicedDate');
        $query->setOrder('DESC');

        $this->assertEquals(2, $query->getPage());
        $this->assertEquals(25, $query->getLimit());
        $this->assertEquals('invoicedDate', $query->getSort());
        $this->assertEquals('DESC', $query->getOrder());
    }
}
