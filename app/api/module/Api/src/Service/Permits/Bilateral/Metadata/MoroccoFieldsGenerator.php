<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;

class MoroccoFieldsGenerator implements FieldsGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(IrhpPermitStock $irhpPermitStock, ?IrhpPermitApplication $irhpPermitApplication)
    {
        $value = null;
        if (
            is_object($irhpPermitApplication) &&
            $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock() === $irhpPermitStock
        ) {
            $bilateralRequired = $irhpPermitApplication->getBilateralRequired();
            $value = $bilateralRequired[IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED];
        }

        return [
            'name' => 'permitsRequired',
            'caption' => $irhpPermitStock->getPeriodNameKey(),
            'value' => $value,
        ];
    }
}
