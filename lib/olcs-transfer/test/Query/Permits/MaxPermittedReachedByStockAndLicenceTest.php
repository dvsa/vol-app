<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByStockAndLicence;

/**
 * Max permitted reached by stock and licence test
 */
final class MaxPermittedReachedByStockAndLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpPermitStockId = 40;
        $licenceId = 9;

        $query = MaxPermittedReachedByStockAndLicence::create(
            [
                'irhpPermitStock' => $irhpPermitStockId,
                'licence' => $licenceId
            ]
        );

        $this->assertEquals($irhpPermitStockId, $query->getIrhpPermitStock());
        $this->assertEquals($licenceId, $query->getLicence());
    }
}
