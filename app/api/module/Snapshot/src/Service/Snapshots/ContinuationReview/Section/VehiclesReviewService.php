<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Vehicles Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        $notRemovedCriteria = Criteria::create();
        $notRemovedCriteria->andWhere(
            $notRemovedCriteria->expr()->isNull('removalDate')
        );

        $licenceVehicles = $continuationDetail->getLicence()->getLicenceVehicles()->matching($notRemovedCriteria);
        $isGoods =
            $continuationDetail->getLicence()->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $header[] = [
            ['value' => 'continuations.vehicles-section.table.vrm', 'header' => true]
        ];
        if ($isGoods) {
            $header[0][] = ['value' => 'continuations.vehicles-section.table.weight', 'header' => true];
        }

        $config = [];
        /** @var LicenceVehicle $lv */
        foreach ($licenceVehicles as $lv) {
            /** @var Vehicle $vehicle */
            $vehicle = $lv->getVehicle();
            $row = [];
            $row[] = ['value' => $vehicle->getVrm()];
            if ($isGoods) {
                $row[] = [
                    // no need to translate, the same in Welsh
                    'value' => $vehicle->getPlatedWeight() . 'kg'
                ];
            }
            $config[] = $row;
        }

        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );

        return (count($config) === 0)
            ? ['emptyTableMessage' => $this->translate('There are no vehicles recorded on your licence')]
            : array_merge($header, $config);
    }
}
