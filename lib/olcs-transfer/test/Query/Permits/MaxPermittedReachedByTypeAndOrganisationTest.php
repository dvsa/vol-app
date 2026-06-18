<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByTypeAndOrganisation;

/**
 * Max permitted reached by type and organisation test
 */
class MaxPermittedReachedByTypeAndOrganisationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpPermitTypeId = 3;
        $organisationId = 88;

        $query = MaxPermittedReachedByTypeAndOrganisation::create(
            [
                'irhpPermitType' => $irhpPermitTypeId,
                'organisation' => $organisationId,
            ]
        );

        $this->assertEquals($irhpPermitTypeId, $query->getIrhpPermitType());
        $this->assertEquals($organisationId, $query->getOrganisation());
    }
}
