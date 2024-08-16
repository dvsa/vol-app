<?php

/**
 * Variation Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\VariationOperatingCentre;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\ApplicationOperatingCentre\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Query\LicenceOperatingCentre\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;

/**
 * Variation Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentre extends AbstractQueryHandler
{
    public function handleQuery(QueryInterface $query)
    {
        $id = $query->getId();

        [$prefix, $id] = $this->splitTypeAndId($id);

        if (!in_array($prefix, ['L', 'A'])) {
            throw new \Exception('Couldn\'t determine identity');
        }

        if ($prefix === 'L') {
            /** @var Result $response */
            $response = $this->getQueryHandler()->handleQuery(
                LicenceOperatingCentre::create(
                    [
                        'id' => $id,
                        'isVariation' => true
                    ]
                ),
                false
            );
            $response->setValue('canUpdateAddress', false);
        } else {
            /* @var $response Result  */
            $response = $this->getQueryHandler()->handleQuery(ApplicationOperatingCentre::create(['id' => $id]), false);

            $aocData = $response->serialize();
            $response->setValue('canUpdateAddress', isset($aocData['action']) && $aocData['action'] === 'A');
        }

        return $response;
    }

    private function splitTypeAndId($ref)
    {
        $type = substr((string) $ref, 0, 1);

        $id = (int)substr((string) $ref, 1);

        return [$type, $id];
    }
}
