<?php

namespace Olcs\Service\Qa\ViewGenerator;

use Permits\View\Helper\IrhpApplicationSection;

class IrhpPermitApplicationViewGenerator implements ViewGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'permits/single-question-bilateral';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalViewVariables(array $result)
    {
        return [
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'countryName' => $result['additionalViewData']['countryName']
            ],
        ];
    }
}
