<?php

namespace Dvsa\OlcsTest\Transfer\Query\CommunityLic;

use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences;

/**
 *  Community licences test
 */
class CommunityLicencesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = CommunityLicences::create(
            [
                'sort' => 'id',
                'order' => 'ASC',
                'statuses' => 'foo',
                'licence' => 1
            ]
        );
        $this->assertEquals(1, $query->getLicence());
        $this->assertEquals('foo', $query->getStatuses());
        $this->assertEquals('ASC', $query->getOrder());
        $this->assertEquals('id', $query->getSort());
    }
}
