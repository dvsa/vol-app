<?php

declare(strict_types=1);

namespace CommonTest\Common\Data\Object\Search;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Data\Object\Search\LicenceSelfserve::class)]
final class LicenceSelfserveTest extends SearchAbstractTest
{
    protected $class = \Common\Data\Object\Search\LicenceSelfserve::class;

    public function testOrgNameFormatter(): void
    {
        $col = $this->sut->getColumns()[2];

        //  count of Licence == 0
        $data = [
            'orgName' => 'unit_OrgName',
            'noOfLicencesHeld' => 0,
        ];

        $this->assertEquals('unit_OrgName', $col['formatter']($data));

        //  count of Licence > 0
        $data = [
            'orgName' => 'unit_OrgName',
            'noOfLicencesHeld' => 2,
        ];

        $this->assertEquals('unit_OrgName (MLH)', $col['formatter']($data));
    }
}
