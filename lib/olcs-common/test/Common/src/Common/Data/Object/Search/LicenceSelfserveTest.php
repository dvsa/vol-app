<?php

namespace CommonTest\Common\Data\Object\Search;

/**
 * @covers \Common\Data\Object\Search\LicenceSelfserve
 */
class LicenceSelfserveTest extends SearchAbstractTest
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

        static::assertEquals('unit_OrgName', $col['formatter']($data));

        //  count of Licence > 0
        $data = [
            'orgName' => 'unit_OrgName',
            'noOfLicencesHeld' => 2,
        ];

        static::assertEquals('unit_OrgName (MLH)', $col['formatter']($data));
    }
}
